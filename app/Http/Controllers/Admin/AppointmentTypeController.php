<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentTypeRequest;
use App\Http\Requests\Appointment\UpdateAppointmentTypeRequest;
use App\Models\AppointmentType;
use App\Services\Appointment\AppointmentTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentTypeController extends Controller
{
    public function __construct(private readonly AppointmentTypeService $types) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('appointments.manage-all'), 403);

        $types = AppointmentType::query()
            ->withCount('appointments')
            ->when($request->boolean('archived'), fn ($query) => $query->onlyTrashed())
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.appointment-types.index', compact('types'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('appointments.manage-all'), 403);

        return view('admin.appointment-types.create', ['type' => new AppointmentType()]);
    }

    public function store(StoreAppointmentTypeRequest $request): RedirectResponse
    {
        $type = $this->types->create($request->validated(), $request->user());

        return redirect()->route('admin.appointment-types.show', $type)->with('status', 'Appointment type created.');
    }

    public function show(Request $request, AppointmentType $appointmentType): View
    {
        abort_unless($request->user()->can('appointments.manage-all'), 403);

        return view('admin.appointment-types.show', ['type' => $appointmentType->loadCount('appointments')]);
    }

    public function edit(Request $request, AppointmentType $appointmentType): View
    {
        abort_unless($request->user()->can('appointments.manage-all'), 403);

        return view('admin.appointment-types.edit', ['type' => $appointmentType]);
    }

    public function update(UpdateAppointmentTypeRequest $request, AppointmentType $appointmentType): RedirectResponse
    {
        $this->types->update($appointmentType, $request->validated(), $request->user());

        return redirect()->route('admin.appointment-types.show', $appointmentType)->with('status', 'Appointment type updated.');
    }

    public function destroy(Request $request, AppointmentType $appointmentType): RedirectResponse
    {
        abort_unless($request->user()->can('appointments.manage-all'), 403);
        $this->types->archive($appointmentType, $request->user());

        return redirect()->route('admin.appointment-types.index')->with('status', 'Appointment type archived.');
    }
}
