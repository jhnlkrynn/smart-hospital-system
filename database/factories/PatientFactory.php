<?php

namespace Database\Factories;

use App\Enums\PatientStatus;
use App\Enums\Sex;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'patient_number' => 'PAT-'.now('Asia/Manila')->format('Y').'-'.fake()->unique()->numberBetween(100000, 999999),
            'qr_token' => Str::random(64),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => fake()->dateTimeBetween('-75 years', '-1 year')->format('Y-m-d'),
            'sex' => fake()->randomElement(Sex::cases()),
            'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed']),
            'email' => fake()->unique()->safeEmail(),
            'contact_number' => fake()->phoneNumber(),
            'registration_date' => now('Asia/Manila')->toDateString(),
            'status' => PatientStatus::Active,
        ];
    }
}
