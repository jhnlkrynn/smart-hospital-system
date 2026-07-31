<?php

namespace App\Http\Controllers\Patient;

use App\Enums\ConsultationStatus;
use App\Enums\MedicalCertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\MedicalCertificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient, 403);

        $consultations = Consultation::query()
            ->with(['doctor', 'department', 'diagnoses'])
            ->where('patient_id', $patient->id)
            ->where('status', ConsultationStatus::Completed->value)
            ->where('is_patient_visible', true)
            ->latest('completed_at')
            ->paginate(15);

        $certificates = MedicalCertificate::query()
            ->where('patient_id', $patient->id)
            ->where('status', MedicalCertificateStatus::Issued->value)
            ->latest('issued_at')
            ->get();

        return view('patient.medical-records.index', compact('consultations', 'certificates'));
    }

    public function show(Request $request, Consultation $consultation): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient && (int) $consultation->patient_id === (int) $patient->id, 403);
        abort_unless($consultation->status === ConsultationStatus::Completed && $consultation->is_patient_visible, 404);

        return view('patient.medical-records.show', [
            'consultation' => $consultation->load(['doctor', 'department', 'diagnoses', 'medicalCertificates']),
        ]);
    }
}
