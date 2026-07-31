<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\ConsultationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\CancelConsultationRequest;
use App\Http\Requests\Consultation\CompleteConsultationRequest;
use App\Http\Requests\Consultation\UpdateConsultationRequest;
use App\Models\Consultation;
use App\Models\DiagnosisCatalog;
use App\Models\PatientQueue;
use App\Services\Consultation\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private readonly ConsultationService $consultations) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor, 403);

        $consultations = Consultation::query()
            ->with(['patient', 'department', 'queue'])
            ->forDoctor($doctor->id)
            ->search($request->string('search')->toString())
            ->byStatus($request->string('status')->toString())
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('doctor.consultations.index', [
            'consultations' => $consultations,
            'statuses' => ConsultationStatus::cases(),
        ]);
    }

    public function start(Request $request, PatientQueue $queue): RedirectResponse
    {
        abort_unless($request->user()->can('consultations.start'), 403);
        $consultation = $this->consultations->startFromQueue($queue, $request->user());

        return redirect()->route('doctor.consultations.show', $consultation)->with('status', 'Consultation started.');
    }

    public function show(Request $request, Consultation $consultation): View
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());

        $consultation->load([
            'patient.allergies',
            'patient.conditions',
            'queue.triageRecord.vitalSign',
            'diagnoses.diagnosisCatalog',
            'attachments',
            'medicalCertificates',
            'appointment',
            'department',
        ]);

        return view('doctor.consultations.show', [
            'consultation' => $consultation,
            'catalog' => DiagnosisCatalog::query()->where('is_active', true)->orderBy('name')->limit(200)->get(),
        ]);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->consultations->update($consultation, $request->validated(), $request->user());

        return back()->with('status', 'Consultation updated.');
    }

    public function complete(CompleteConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->consultations->complete($consultation, $request->validated(), $request->user());

        return redirect()->route('doctor.consultations.show', $consultation)->with('status', 'Consultation completed.');
    }

    public function cancel(CancelConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->consultations->cancel($consultation, $request->validated(), $request->user());

        return redirect()->route('doctor.consultations.index')->with('status', 'Consultation cancelled.');
    }
}
