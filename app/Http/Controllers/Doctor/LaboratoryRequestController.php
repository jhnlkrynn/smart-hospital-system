<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\StoreLaboratoryRequestRequest;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryResult;
use App\Models\LaboratoryTest;
use App\Services\Laboratory\LaboratoryRequestService;
use App\Services\Laboratory\LaboratoryResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratoryRequestController extends Controller
{
    public function __construct(
        private readonly LaboratoryRequestService $requests,
        private readonly LaboratoryResultService $results,
    ) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor, 403);

        return view('doctor.laboratory.index', [
            'requests' => LaboratoryRequest::query()->with(['patient', 'items', 'results'])->where('requesting_doctor_employee_id', $doctor->id)->latest()->paginate(20),
        ]);
    }

    public function create(Request $request, Consultation $consultation): View
    {
        abort_unless((int) $consultation->doctor_employee_id === (int) $request->user()->employee?->id, 403);

        return view('doctor.laboratory.create', [
            'consultation' => $consultation->load('patient'),
            'tests' => LaboratoryTest::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLaboratoryRequestRequest $request, Consultation $consultation): RedirectResponse
    {
        $labRequest = $this->requests->createFromConsultation($consultation, $request->validated(), $request->user());

        return redirect()->route('doctor.laboratory-requests.show', $labRequest)->with('status', 'Laboratory request created.');
    }

    public function show(Request $request, LaboratoryRequest $laboratoryRequest): View
    {
        abort_unless((int) $laboratoryRequest->requesting_doctor_employee_id === (int) $request->user()->employee?->id, 403);

        return view('doctor.laboratory.show', ['laboratoryRequest' => $laboratoryRequest->load(['patient', 'items.result', 'specimens', 'results.acknowledgments'])]);
    }

    public function acknowledge(Request $request, LaboratoryResult $result): RedirectResponse
    {
        abort_unless($request->user()->can('laboratory-results.acknowledge'), 403);
        $this->results->acknowledge($result, $request->user(), $request->input('notes'));

        return back()->with('status', 'Laboratory result acknowledged.');
    }
}
