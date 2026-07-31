<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AllergySeverity;
use App\Enums\AllergyType;
use App\Enums\PatientConditionStatus;
use App\Enums\PatientDocumentType;
use App\Enums\PatientStatus;
use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreEmergencyContactRequest;
use App\Http\Requests\Patient\StorePatientDocumentRequest;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Services\Audit\AuditLogService;
use App\Services\Patient\EmergencyContactService;
use App\Services\Patient\PatientAllergyService;
use App\Services\Patient\PatientConditionService;
use App\Services\Patient\PatientDocumentService;
use App\Services\Patient\PatientQrService;
use App\Services\Patient\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patient::class);

        $sort = in_array($request->input('sort'), ['last_name', 'patient_number', 'registration_date', 'created_at', 'updated_at'], true)
            ? $request->input('sort')
            : 'last_name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = Patient::with('user')
            ->search($request->input('search'))
            ->byStatus($request->input('status'))
            ->registeredBetween($request->input('registered_from'), $request->input('registered_to'));

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }
        if ($request->filled('blood_type')) {
            $query->where('blood_type', $request->input('blood_type'));
        }
        if ($request->input('account') === 'linked') {
            $query->whereNotNull('user_id');
        } elseif ($request->input('account') === 'unlinked') {
            $query->whereNull('user_id');
        }
        if ($request->input('archived') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('archived') === 'with') {
            $query->withTrashed();
        }

        return view('admin.patients.index', [
            'patients' => $query->orderBy($sort, $direction)->paginate(10)->withQueryString(),
            'statuses' => PatientStatus::cases(),
            'sexes' => Sex::cases(),
            'filters' => $request->all(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        return view('admin.patients.create', $this->formData(new Patient(['status' => PatientStatus::Active])));
    }

    public function store(StorePatientRequest $request, PatientService $patients): RedirectResponse
    {
        $duplicates = $patients->findPossibleDuplicates($request->validated());

        if ($duplicates->isNotEmpty() && ! $request->boolean('duplicate_override')) {
            return back()->withInput()->with('possible_duplicates', $duplicates);
        }

        $patient = $request->boolean('create_account')
            ? $patients->createWithAccount($request->validated(), $request->user())
            : $patients->createWithoutAccount($request->validated(), $request->user());

        return redirect()->route('admin.patients.show', $patient)->with('status', 'Patient registered successfully.');
    }

    public function show(Patient $patient, PatientQrService $qr, AuditLogService $auditLog): View
    {
        $this->authorize('view', $patient);
        $patient->load(['user', 'emergencyContacts', 'allergies.recordedBy', 'conditions.recordedBy', 'documents.uploadedBy']);

        if ($patient->user_id !== request()->user()->id) {
            $auditLog->record(request()->user(), 'viewed', 'patients', $patient, 'Patient profile viewed by staff.', null, null, request());
        }

        return view('admin.patients.show', [
            'patient' => $patient,
            'qrImage' => request()->user()->can('viewQr', $patient) ? $qr->generateQrImage($patient) : null,
            'allergyTypes' => AllergyType::cases(),
            'allergySeverities' => AllergySeverity::cases(),
            'conditionStatuses' => PatientConditionStatus::cases(),
            'documentTypes' => PatientDocumentType::cases(),
        ]);
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        return view('admin.patients.edit', $this->formData($patient));
    }

    public function update(UpdatePatientRequest $request, Patient $patient, PatientService $patients): RedirectResponse
    {
        $patients->update($patient, $request->validated(), $request->user());

        return redirect()->route('admin.patients.show', $patient)->with('status', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient, PatientService $patients): RedirectResponse
    {
        $this->authorize('delete', $patient);
        $patients->archive($patient, request()->user());

        return redirect()->route('admin.patients.index')->with('status', 'Patient archived successfully.');
    }

    public function restore(int $patient, PatientService $patients): RedirectResponse
    {
        $patient = Patient::onlyTrashed()->findOrFail($patient);
        $this->authorize('restore', $patient);
        $patients->restore($patient, request()->user());

        return redirect()->route('admin.patients.show', $patient)->with('status', 'Patient restored successfully.');
    }

    public function regenerateQr(Patient $patient, PatientQrService $qr): RedirectResponse
    {
        $this->authorize('update', $patient);
        $qr->regenerateToken($patient, request()->user());

        return back()->with('status', 'Patient QR token regenerated successfully.');
    }

    public function storeEmergencyContact(StoreEmergencyContactRequest $request, Patient $patient, EmergencyContactService $contacts): RedirectResponse
    {
        $contacts->create($patient, $request->validated(), $request->user());

        return back()->with('status', 'Emergency contact saved.');
    }

    public function storeAllergy(Request $request, Patient $patient, PatientAllergyService $allergies): RedirectResponse
    {
        abort_unless($request->user()->can('patients.manage-allergies'), 403);
        $data = $request->validate([
            'allergen' => ['required', 'string', 'max:255'],
            'allergy_type' => ['required', Rule::enum(AllergyType::class)],
            'reaction' => ['nullable', 'string', 'max:255'],
            'severity' => ['required', Rule::enum(AllergySeverity::class)],
            'diagnosed_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $allergies->add($patient, $data, $request->user());

        return back()->with('status', 'Allergy recorded.');
    }

    public function storeCondition(Request $request, Patient $patient, PatientConditionService $conditions): RedirectResponse
    {
        abort_unless($request->user()->can('patients.manage-conditions'), 403);
        $data = $request->validate([
            'condition_name' => ['required', 'string', 'max:255'],
            'diagnosis_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(PatientConditionStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $conditions->add($patient, $data, $request->user());

        return back()->with('status', 'Condition recorded.');
    }

    public function storeDocument(StorePatientDocumentRequest $request, Patient $patient, PatientDocumentService $documents): RedirectResponse
    {
        $documents->upload($patient, $request->file('document'), $request->validated(), $request->user());

        return back()->with('status', 'Document uploaded securely.');
    }

    public function downloadDocument(Patient $patient, PatientDocument $document, PatientDocumentService $documents)
    {
        abort_unless($document->patient_id === $patient->id, 404);
        abort_unless(request()->user()->can('patients.download-documents'), 403);

        return Response::download($documents->download($document, request()->user()), $document->original_filename);
    }

    private function formData(Patient $patient): array
    {
        return [
            'patient' => $patient,
            'statuses' => PatientStatus::cases(),
            'sexes' => Sex::cases(),
            'bloodTypes' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        ];
    }
}
