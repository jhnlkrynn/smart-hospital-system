<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConsultationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\ReopenConsultationRequest;
use App\Models\Consultation;
use App\Services\Consultation\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private readonly ConsultationService $consultations) {}

    public function index(Request $request): View
    {
        $consultations = Consultation::query()
            ->with(['patient', 'doctor', 'department'])
            ->search($request->string('search')->toString())
            ->byStatus($request->string('status')->toString())
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.consultations.index', [
            'consultations' => $consultations,
            'statuses' => ConsultationStatus::cases(),
        ]);
    }

    public function show(Consultation $consultation): View
    {
        return view('admin.consultations.show', [
            'consultation' => $consultation->load(['patient', 'doctor', 'department', 'diagnoses', 'medicalCertificates']),
        ]);
    }

    public function reopen(ReopenConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->reopen($consultation, $request->validated('reason'), $request->user());

        return back()->with('status', 'Consultation reopened.');
    }
}
