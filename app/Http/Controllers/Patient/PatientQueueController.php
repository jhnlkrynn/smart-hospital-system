<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientQueue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientQueueController extends Controller
{
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient && $request->user()->can('queues.view-own-status'), 403);

        $queues = PatientQueue::query()
            ->with('department')
            ->where('patient_id', $patient->id)
            ->latest('queue_date')
            ->latest('id')
            ->paginate(10);

        return view('patient.queues.index', compact('queues'));
    }
}
