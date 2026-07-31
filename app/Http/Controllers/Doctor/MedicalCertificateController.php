<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreMedicalCertificateRequest;
use App\Http\Requests\Consultation\UpdateMedicalCertificateRequest;
use App\Http\Requests\Consultation\VoidMedicalCertificateRequest;
use App\Models\Consultation;
use App\Models\MedicalCertificate;
use App\Services\Consultation\ConsultationService;
use App\Services\Consultation\MedicalCertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalCertificateController extends Controller
{
    public function __construct(
        private readonly MedicalCertificateService $certificates,
        private readonly ConsultationService $consultations,
    ) {}

    public function store(StoreMedicalCertificateRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->certificates->create($consultation, $request->validated(), $request->user());

        return back()->with('status', 'Medical certificate draft created.');
    }

    public function show(Consultation $consultation, MedicalCertificate $certificate): View
    {
        abort_unless((int) $certificate->consultation_id === (int) $consultation->id, 404);

        return view('doctor.medical-certificates.show', compact('consultation', 'certificate'));
    }

    public function update(UpdateMedicalCertificateRequest $request, Consultation $consultation, MedicalCertificate $certificate): RedirectResponse
    {
        abort_unless((int) $certificate->consultation_id === (int) $consultation->id, 404);
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->certificates->update($certificate, $request->validated(), $request->user());

        return back()->with('status', 'Medical certificate updated.');
    }

    public function issue(Consultation $consultation, MedicalCertificate $certificate): RedirectResponse
    {
        abort_unless((int) $certificate->consultation_id === (int) $consultation->id, 404);
        $this->certificates->issue($certificate, request()->user());

        return back()->with('status', 'Medical certificate issued.');
    }

    public function voidCertificate(VoidMedicalCertificateRequest $request, Consultation $consultation, MedicalCertificate $certificate): RedirectResponse
    {
        abort_unless((int) $certificate->consultation_id === (int) $consultation->id, 404);
        $this->certificates->voidCertificate($certificate, $request->validated('reason'), $request->user());

        return back()->with('status', 'Medical certificate voided.');
    }
}
