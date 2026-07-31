<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RejectAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Employee;
use App\Models\Patient;
use App\Services\Appointment\AppointmentService;
use App\Services\Appointment\DoctorAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointments,
        private readonly DoctorAvailabilityService $availability,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('appointments.view') || $request->user()->can('appointments.view-all'), 403);

        $allowedSorts = ['appointment_date', 'appointment_number', 'status', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'appointment_date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $appointments = Appointment::query()
            ->with(['patient', 'doctor.department', 'appointmentType'])
            ->search($request->input('search'))
            ->byStatus($request->input('status'))
            ->betweenDates($request->input('from'), $request->input('to'))
            ->orderBy($sort, $direction)
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('appointments.create-for-patient'), 403);

        return view('admin.appointments.create', $this->formData());
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $source = $request->user()->hasRole('hospital-admin') || $request->user()->hasRole('super-admin')
            ? AppointmentSource::Admin
            : AppointmentSource::Staff;
        $appointment = $this->appointments->create($request->validated(), $request->user(), $source);

        return redirect()->route('admin.appointments.show', $appointment)->with('status', 'Appointment booked.');
    }

    public function show(Request $request, Appointment $appointment): View
    {
        abort_unless($request->user()->can('appointments.view') || $request->user()->can('appointments.view-all'), 403);

        return view('admin.appointments.show', ['appointment' => $appointment->load(['patient', 'doctor.department', 'appointmentType', 'statusHistories.changedBy'])]);
    }

    public function approve(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.approve'), 403);
        $this->appointments->approve($appointment, $request->user());

        return back()->with('status', 'Appointment approved.');
    }

    public function reject(RejectAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->appointments->reject($appointment, $request->user(), $request->validated('rejection_reason'));

        return back()->with('status', 'Appointment rejected.');
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->appointments->cancel($appointment, $request->user(), $request->validated('cancellation_reason'));

        return back()->with('status', 'Appointment cancelled.');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.complete'), 403);
        $this->appointments->complete($appointment, $request->user());

        return back()->with('status', 'Appointment completed.');
    }

    public function markNoShow(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.mark-no-show'), 403);
        $this->appointments->markNoShow($appointment, $request->user());

        return back()->with('status', 'Appointment marked no-show.');
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $new = $this->appointments->reschedule($appointment, $request->validated(), $request->user(), AppointmentSource::Staff);

        return redirect()->route('admin.appointments.show', $new)->with('status', 'Appointment rescheduled.');
    }

    public function slots(Request $request)
    {
        abort_unless($request->user()->can('appointments.create') || $request->user()->can('appointments.create-for-patient'), 403);
        $doctor = Employee::with('user.roles')->findOrFail($request->integer('doctor_employee_id'));

        return response()->json($this->availability->availableSlots($doctor, $request->input('date'), $request->integer('patient_id'))->values());
    }

    private function formData(): array
    {
        return [
            'patients' => Patient::active()->orderBy('last_name')->get(),
            'doctors' => Employee::active()->doctors()->with('department')->orderBy('last_name')->get(),
            'types' => AppointmentType::where('is_active', true)->orderBy('code')->get(),
        ];
    }
}
