<?php

namespace App\Http\Controllers\Patient;

use App\Enums\AppointmentSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Appointment\StoreOwnAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Employee;
use App\Services\Appointment\AppointmentService;
use App\Services\Appointment\DoctorAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientAppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointments,
        private readonly DoctorAvailabilityService $availability,
    ) {}

    public function index(Request $request): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient && $request->user()->can('appointments.view-own'), 403);

        $appointments = Appointment::with(['doctor.department', 'appointmentType'])
            ->forPatient($patient->id)
            ->orderByDesc('appointment_date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('patient.appointments.index', compact('appointments'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->patient && $request->user()->can('appointments.create'), 403);

        return view('patient.appointments.create', $this->formData($request));
    }

    public function store(StoreOwnAppointmentRequest $request): RedirectResponse
    {
        $appointment = $this->appointments->create($request->validated() + [
            'patient_id' => $request->user()->patient->id,
        ], $request->user(), AppointmentSource::PatientPortal);

        return redirect()->route('patient.appointments.show', $appointment)->with('status', 'Appointment request submitted.');
    }

    public function show(Request $request, Appointment $appointment): View
    {
        abort_unless((int) $appointment->patient_id === (int) $request->user()->patient?->id, 403);

        return view('patient.appointments.show', ['appointment' => $appointment->load(['doctor.department', 'appointmentType', 'statusHistories.changedBy'])]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->patient_id === (int) $request->user()->patient?->id, 403);
        $this->appointments->cancel($appointment, $request->user(), $request->validated('cancellation_reason'));

        return back()->with('status', 'Appointment cancelled.');
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->patient_id === (int) $request->user()->patient?->id, 403);
        $new = $this->appointments->reschedule($appointment, $request->validated(), $request->user(), AppointmentSource::PatientPortal);

        return redirect()->route('patient.appointments.show', $new)->with('status', 'Appointment rescheduled.');
    }

    public function slots(Request $request)
    {
        abort_unless($request->user()->patient && $request->user()->can('appointments.create'), 403);
        $doctor = Employee::with('user.roles')->findOrFail($request->integer('doctor_employee_id'));

        return response()->json($this->availability->availableSlots($doctor, $request->input('date'), $request->user()->patient->id)->values());
    }

    private function formData(Request $request): array
    {
        return [
            'doctors' => Employee::active()->doctors()->with('department')->orderBy('last_name')->get(),
            'types' => AppointmentType::where('is_active', true)->orderBy('code')->get(),
        ];
    }
}
