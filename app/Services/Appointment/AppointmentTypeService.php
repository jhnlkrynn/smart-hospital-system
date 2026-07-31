<?php

namespace App\Services\Appointment;

use App\Models\AppointmentType;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentTypeService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function create(array $data, User $actor): AppointmentType
    {
        return DB::transaction(function () use ($data, $actor): AppointmentType {
            $type = AppointmentType::create($data + ['created_by' => $actor->id, 'updated_by' => $actor->id]);
            $this->audit->record($actor, 'appointment_type.created', 'appointments', $type, 'Appointment type created.', null, $type->only(['code', 'name', 'default_duration_minutes']));

            return $type;
        });
    }

    public function update(AppointmentType $type, array $data, User $actor): AppointmentType
    {
        return DB::transaction(function () use ($type, $data, $actor): AppointmentType {
            $old = $type->only(array_keys($data));
            $type->update($data + ['updated_by' => $actor->id]);
            $this->audit->record($actor, 'appointment_type.updated', 'appointments', $type, 'Appointment type updated.', $old, $type->only(array_keys($data)));

            return $type;
        });
    }

    public function archive(AppointmentType $type, User $actor): void
    {
        DB::transaction(function () use ($type, $actor): void {
            $type->delete();
            $this->audit->record($actor, 'appointment_type.archived', 'appointments', $type, 'Appointment type archived.');
        });
    }
}
