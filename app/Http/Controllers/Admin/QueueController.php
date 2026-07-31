<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\CheckInAppointmentRequest;
use App\Http\Requests\Queue\QueueTransitionRequest;
use App\Http\Requests\Queue\StoreWalkInQueueRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Services\Queue\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function __construct(private readonly QueueService $queues) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('queues.view'), 403);

        $queues = PatientQueue::query()
            ->with(['patient.allergies', 'department', 'doctor', 'appointment'])
            ->when(! $request->filled('date'), fn ($query) => $query->today())
            ->when($request->filled('date'), fn ($query) => $query->whereDate('queue_date', $request->input('date')))
            ->forDepartment($request->input('department_id'))
            ->byStatus($request->input('status'))
            ->waitingOrder()
            ->paginate(20)
            ->withQueryString();

        return view('admin.queues.index', [
            'queues' => $queues,
            'departments' => Department::active()->orderBy('name')->get(),
            'statuses' => QueueStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('queues.manage'), 403);

        return view('admin.queues.create', [
            'patients' => Patient::active()->orderBy('last_name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'doctors' => Employee::active()->doctors()->orderBy('last_name')->get(),
        ]);
    }

    public function store(StoreWalkInQueueRequest $request): RedirectResponse
    {
        $patient = Patient::findOrFail($request->validated('patient_id'));
        $department = Department::findOrFail($request->validated('department_id'));
        $queue = $this->queues->createWalkIn($patient, $department, $request->user(), $request->validated());

        return redirect()->route('admin.queues.show', $queue)->with('status', 'Walk-in patient added to queue.');
    }

    public function show(Request $request, PatientQueue $queue): View
    {
        abort_unless($request->user()->can('queues.view'), 403);

        return view('admin.queues.show', [
            'queue' => $queue->load(['patient.allergies', 'department', 'doctor', 'appointment', 'histories.changedBy', 'triageRecord.vitalSign', 'vitalSigns']),
            'statuses' => QueueStatus::cases(),
        ]);
    }

    public function checkIn(CheckInAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $queue = $this->queues->checkInAppointment($appointment, $request->user(), $request->validated());

        return redirect()->route('admin.queues.show', $queue)->with('status', 'Appointment checked in.');
    }

    public function callNext(Request $request, Department $department): RedirectResponse
    {
        abort_unless($request->user()->can('queues.call'), 403);
        $queue = $this->queues->callNext($department, $request->user());

        return redirect()->route('admin.queues.show', $queue)->with('status', 'Next patient called.');
    }

    public function startDoctor(QueueTransitionRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->queues->transition($queue, QueueStatus::WithDoctor, $request->user(), notes: $request->validated('notes'));

        return back()->with('status', 'Patient sent to doctor.');
    }

    public function complete(QueueTransitionRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->queues->transition($queue, QueueStatus::Completed, $request->user(), notes: $request->validated('notes'));

        return back()->with('status', 'Queue completed.');
    }

    public function skip(QueueTransitionRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->queues->transition($queue, QueueStatus::Skipped, $request->user(), notes: $request->validated('notes'));

        return back()->with('status', 'Queue skipped.');
    }

    public function cancel(QueueTransitionRequest $request, PatientQueue $queue): RedirectResponse
    {
        $this->queues->transition($queue, QueueStatus::Cancelled, $request->user(), notes: $request->validated('notes'));

        return back()->with('status', 'Queue cancelled.');
    }
}
