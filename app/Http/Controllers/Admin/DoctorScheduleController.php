<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DayOfWeek;
use App\Enums\ScheduleExceptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSchedule\StoreDoctorScheduleRequest;
use App\Http\Requests\DoctorSchedule\StoreScheduleExceptionRequest;
use App\Http\Requests\DoctorSchedule\UpdateDoctorScheduleRequest;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Services\Appointment\DoctorAvailabilityService;
use App\Services\Appointment\DoctorScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function __construct(
        private readonly DoctorScheduleService $schedules,
        private readonly DoctorAvailabilityService $availability,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('doctor-schedules.view'), 403);

        $doctorId = $request->integer('doctor_id') ?: null;
        $schedules = DoctorSchedule::query()
            ->with('doctor.department')
            ->when($doctorId, fn ($query) => $query->forDoctor($doctorId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.doctor-schedules.index', [
            'schedules' => $schedules,
            'doctors' => $this->doctors(),
            'dayOptions' => DayOfWeek::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('doctor-schedules.create') || $request->user()->can('doctor-schedules.manage-own'), 403);

        return view('admin.doctor-schedules.create', [
            'schedule' => new DoctorSchedule(),
            'doctors' => $this->doctors(),
            'dayOptions' => DayOfWeek::cases(),
        ]);
    }

    public function store(StoreDoctorScheduleRequest $request): RedirectResponse
    {
        $schedule = $this->schedules->createSchedule($request->validated(), $request->user());

        return redirect()->route('admin.doctor-schedules.show', $schedule)->with('status', 'Doctor schedule created.');
    }

    public function show(Request $request, DoctorSchedule $doctorSchedule): View
    {
        abort_unless($request->user()->can('doctor-schedules.view'), 403);

        $date = $request->input('date', now('Asia/Manila')->next($doctorSchedule->day_of_week->carbonIso())->toDateString());

        return view('admin.doctor-schedules.show', [
            'schedule' => $doctorSchedule->load('doctor.department'),
            'slots' => $this->availability->availableSlots($doctorSchedule->doctor, $date),
            'date' => $date,
        ]);
    }

    public function edit(Request $request, DoctorSchedule $doctorSchedule): View
    {
        abort_unless($request->user()->can('doctor-schedules.update') || $request->user()->can('doctor-schedules.manage-own'), 403);

        return view('admin.doctor-schedules.edit', [
            'schedule' => $doctorSchedule,
            'doctors' => $this->doctors(),
            'dayOptions' => DayOfWeek::cases(),
        ]);
    }

    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): RedirectResponse
    {
        $this->schedules->updateSchedule($doctorSchedule, $request->validated(), $request->user());

        return redirect()->route('admin.doctor-schedules.show', $doctorSchedule)->with('status', 'Doctor schedule updated.');
    }

    public function destroy(Request $request, DoctorSchedule $doctorSchedule): RedirectResponse
    {
        abort_unless($request->user()->can('doctor-schedules.archive'), 403);
        $this->schedules->archiveSchedule($doctorSchedule, $request->user());

        return redirect()->route('admin.doctor-schedules.index')->with('status', 'Doctor schedule archived.');
    }

    public function exceptions(Request $request): View
    {
        abort_unless($request->user()->can('doctor-schedules.manage-exceptions') || $request->user()->can('doctor-schedules.manage-leave'), 403);

        $exceptions = \App\Models\DoctorScheduleException::query()
            ->with('doctor.department')
            ->latest('exception_date')
            ->paginate(15);

        return view('admin.doctor-schedules.exceptions', [
            'exceptions' => $exceptions,
            'doctors' => $this->doctors(),
            'types' => ScheduleExceptionType::cases(),
        ]);
    }

    public function storeException(StoreScheduleExceptionRequest $request): RedirectResponse
    {
        $this->schedules->createException($request->validated(), $request->user());

        return back()->with('status', 'Schedule exception recorded. Existing appointments were preserved.');
    }

    private function doctors()
    {
        return Employee::query()->with('department', 'user.roles')->active()->doctors()->orderBy('last_name')->get();
    }
}
