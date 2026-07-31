<?php

namespace App\Http\Requests\DoctorSchedule;

class UpdateDoctorScheduleRequest extends StoreDoctorScheduleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['doctor_employee_id'] = ['sometimes', 'integer'];

        return $rules;
    }
}
