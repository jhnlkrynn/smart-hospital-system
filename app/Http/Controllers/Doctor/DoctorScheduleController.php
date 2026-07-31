<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\Appointment\DoctorAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function __construct(private readonly DoctorAvailabilityService $availability) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor && $request->user()->can('doctor-schedules.view'), 403);

        return view('doctor.schedules.index', [
            'doctor' => $doctor->load('doctorSchedules', 'scheduleExceptions'),
            'date' => $request->input('date', now('Asia/Manila')->toDateString()),
            'slots' => $this->availability->availableSlots($doctor, $request->input('date', now('Asia/Manila')->toDateString())),
        ]);
    }
}
