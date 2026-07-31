<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryResult;
use App\Services\Laboratory\LaboratoryReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class PatientLaboratoryResultController extends Controller
{
    public function __construct(private readonly LaboratoryReportService $reports) {}

    public function index(Request $request): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient, 403);

        return view('patient.laboratory-results.index', [
            'results' => LaboratoryResult::query()
                ->with(['laboratoryRequest.doctor', 'laboratoryTest'])
                ->where('patient_id', $patient->id)
                ->whereNotNull('released_at')
                ->where('is_patient_visible', true)
                ->latest('released_at')
                ->paginate(20),
        ]);
    }

    public function show(Request $request, LaboratoryResult $result): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient && (int) $result->patient_id === (int) $patient->id, 403);
        abort_unless($result->released_at && $result->is_patient_visible, 404);

        return view('patient.laboratory-results.show', ['result' => $result->load(['laboratoryRequest.doctor', 'laboratoryTest', 'attachments'])]);
    }

    public function download(Request $request, LaboratoryResult $result): Response
    {
        $patient = $request->user()->patient;
        abort_unless($patient && (int) $result->patient_id === (int) $patient->id, 403);
        abort_unless($result->released_at && $result->is_patient_visible, 404);

        return response($this->reports->pdf($result->load(['laboratoryRequest', 'patient', 'laboratoryTest'])), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$result->laboratoryRequest->request_number.'-'.$result->laboratoryTest->code.'.pdf"',
        ]);
    }
}
