<?php

namespace App\Http\Controllers\Nurse;

use App\Enums\TriageAcuity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\StoreTriageRecordRequest;
use App\Http\Requests\Queue\StoreVitalSignsRequest;
use App\Models\PatientQueue;
use App\Services\Queue\TriageService;
use App\Services\Queue\VitalSignsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TriageController extends Controller
{
    public function __construct(
        private readonly TriageService $triage,
        private readonly VitalSignsService $vitals,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('triage.view'), 403);

        $queues = PatientQueue::query()
            ->with(['patient.allergies', 'department', 'triageRecord'])
            ->today()
            ->whereIn('status', ['called', 'in_triage', 'waiting'])
            ->waitingOrder()
            ->paginate(20);

        return view('nurse.triage.index', compact('queues'));
    }

    public function create(Request $request, PatientQueue $queue): View
    {
        abort_unless($request->user()->can('triage.create'), 403);

        return view('nurse.triage.create', [
            'queue' => $queue->load('patient.allergies'),
            'acuities' => TriageAcuity::cases(),
        ]);
    }

    public function store(StoreTriageRecordRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->triage->record($queue, $request->user(), $request->triageData(), $request->vitalData());

        return redirect()->route('admin.queues.show', $queue)->with('status', 'Triage completed.');
    }

    public function storeVitals(StoreVitalSignsRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->vitals->record($queue, $request->user(), $request->validated());

        return back()->with('status', 'Vital signs recorded.');
    }
}
