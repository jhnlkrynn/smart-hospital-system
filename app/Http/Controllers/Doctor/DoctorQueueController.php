<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Models\PatientQueue;
use App\Services\Queue\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorQueueController extends Controller
{
    public function __construct(private readonly QueueService $queues) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor && $request->user()->can('queues.view'), 403);

        $queues = PatientQueue::query()
            ->with(['patient.allergies', 'triageRecord.vitalSign'])
            ->forDoctor($doctor->id)
            ->today()
            ->whereIn('status', [QueueStatus::Triaged->value, QueueStatus::WithDoctor->value])
            ->waitingOrder()
            ->paginate(20);

        return view('doctor.queues.index', compact('queues'));
    }

    public function start(Request $request, PatientQueue $queue): RedirectResponse
    {
        abort_unless((int) $queue->doctor_employee_id === (int) $request->user()->employee?->id && $request->user()->can('queues.complete'), 403);
        $this->queues->transition($queue, QueueStatus::WithDoctor, $request->user());

        return back()->with('status', 'Patient moved to doctor.');
    }

    public function complete(Request $request, PatientQueue $queue): RedirectResponse
    {
        abort_unless((int) $queue->doctor_employee_id === (int) $request->user()->employee?->id && $request->user()->can('queues.complete'), 403);
        $this->queues->transition($queue, QueueStatus::Completed, $request->user());

        return back()->with('status', 'Queue completed.');
    }
}
