<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Sex;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'employee_number' => 'EMP-'.now('Asia/Manila')->format('Y').'-'.fake()->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'first_name' => $first,
            'middle_name' => null,
            'last_name' => $last,
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-22 years')->format('Y-m-d'),
            'sex' => fake()->randomElement(Sex::cases()),
            'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed']),
            'email' => fake()->unique()->safeEmail(),
            'contact_number' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'employment_status' => EmploymentStatus::Active,
            'hire_date' => fake()->dateTimeBetween('-8 years', 'now')->format('Y-m-d'),
            'license_number' => fake()->optional()->bothify('PRC-#######'),
            'consultation_fee' => null,
            'maximum_appointments_per_day' => null,
            'clinic_room' => null,
        ];
    }
}
