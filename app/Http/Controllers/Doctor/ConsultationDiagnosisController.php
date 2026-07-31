<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreConsultationDiagnosisRequest;
use App\Models\Consultation;
use App\Services\Consultation\ConsultationService;
use App\Services\Consultation\DiagnosisService;
use Illuminate\Http\RedirectResponse;

class ConsultationDiagnosisController extends Controller
{
    public function __construct(
        private readonly DiagnosisService $diagnoses,
        private readonly ConsultationService $consultations,
    ) {}

    public function store(StoreConsultationDiagnosisRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->diagnoses->add($consultation, $request->validated(), $request->user());

        return back()->with('status', 'Diagnosis added.');
    }
}
