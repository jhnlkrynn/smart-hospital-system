<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Appointment\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorAppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $doctor = $request->user()->employee;
        abort_unless($doctor && $request->user()->can('appointments.view-assigned'), 403);

        $appointments = Appointment::with(['patient', 'appointmentType'])
            ->forDoctor($doctor->id)
            ->upcoming()
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('doctor.appointments.index', compact('appointments'));
    }

    public function show(Request $request, Appointment $appointment): View
    {
        abort_unless((int) $appointment->doctor_employee_id === (int) $request->user()->employee?->id, 403);

        return view('doctor.appointments.show', ['appointment' => $appointment->load(['patient', 'appointmentType', 'statusHistories.changedBy'])]);
    }

    public function approve(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->doctor_employee_id === (int) $request->user()->employee?->id && $request->user()->can('appointments.approve'), 403);
        $this->appointments->approve($appointment, $request->user());

        return back()->with('status', 'Appointment approved.');
    }

    public function reject(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->doctor_employee_id === (int) $request->user()->employee?->id && $request->user()->can('appointments.reject'), 403);
        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        $this->appointments->reject($appointment, $request->user(), $request->input('rejection_reason'));

        return back()->with('status', 'Appointment rejected.');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->doctor_employee_id === (int) $request->user()->employee?->id && $request->user()->can('appointments.complete'), 403);
        $this->appointments->complete($appointment, $request->user());

        return back()->with('status', 'Appointment completed.');
    }
}
