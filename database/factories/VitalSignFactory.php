<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VitalSign> */
class VitalSignFactory extends Factory
{
    protected $model = VitalSign::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'recorded_by' => User::factory(),
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'pulse_rate' => 78,
            'respiratory_rate' => 16,
            'temperature_c' => 36.8,
            'oxygen_saturation' => 98,
            'height_cm' => 170,
            'weight_kg' => 70,
            'bmi' => 24.22,
            'measured_at' => now(),
        ];
    }
}
