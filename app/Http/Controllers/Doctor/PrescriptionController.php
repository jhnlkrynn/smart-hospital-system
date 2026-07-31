<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\AcknowledgeAllergyWarningRequest;
use App\Http\Requests\Pharmacy\CancelPrescriptionRequest;
use App\Http\Requests\Pharmacy\StorePrescriptionRequest;
use App\Models\Consultation;
use App\Models\Medication;
use App\Models\MedicationFrequency;
use App\Models\MedicationRoute;
use App\Models\MedicationUnit;
use App\Models\Prescription;
use App\Models\PrescriptionAllergyWarning;
use App\Services\Pharmacy\PrescriptionAllergyService;
use App\Services\Pharmacy\PrescriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionService $prescriptions) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor, 403);

        return view('doctor.prescriptions.index', [
            'prescriptions' => Prescription::query()->with(['patient', 'items'])->where('doctor_employee_id', $doctor->id)->latest()->paginate(20),
        ]);
    }

    public function create(Request $request, Consultation $consultation): View
    {
        abort_unless((int) $consultation->doctor_employee_id === (int) $request->user()->employee?->id, 403);

        return view('doctor.prescriptions.create', [
            'consultation' => $consultation->load(['patient.allergies']),
            'medications' => Medication::query()->prescribable()->with(['dosageForm', 'strengthUnit'])->orderBy('generic_name')->get(),
            'units' => MedicationUnit::query()->orderBy('name')->get(),
            'routes' => MedicationRoute::query()->orderBy('name')->get(),
            'frequencies' => MedicationFrequency::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePrescriptionRequest $request, Consultation $consultation): RedirectResponse
    {
        $prescription = $this->prescriptions->createFromConsultation($consultation, $request->validated(), $request->user());

        return redirect()->route('doctor.prescriptions.show', $prescription)->with('status', 'Prescription saved.');
    }

    public function show(Request $request, Prescription $prescription): View
    {
        abort_unless((int) $prescription->doctor_employee_id === (int) $request->user()->employee?->id, 403);

        return view('doctor.prescriptions.show', ['prescription' => $prescription->load(['patient.allergies', 'consultation', 'items.medication', 'allergyWarnings.patientAllergy'])]);
    }

    public function acknowledge(AcknowledgeAllergyWarningRequest $request, Prescription $prescription, PrescriptionAllergyWarning $warning, PrescriptionAllergyService $allergies): RedirectResponse
    {
        abort_unless((int) $warning->prescription_id === (int) $prescription->id, 404);
        $allergies->acknowledge($warning, $request->user(), $request->validated('override_reason'));

        return back()->with('status', 'Allergy warning acknowledged.');
    }

    public function finalize(Request $request, Prescription $prescription): RedirectResponse
    {
        abort_unless($request->user()->can('prescriptions.finalize'), 403);
        $this->prescriptions->finalize($prescription, $request->user());

        return back()->with('status', 'Prescription finalized.');
    }

    public function cancel(CancelPrescriptionRequest $request, Prescription $prescription): RedirectResponse
    {
        $this->prescriptions->cancel($prescription, $request->user(), $request->validated('reason'));

        return back()->with('status', 'Prescription cancelled.');
    }
}
