<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Sex;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $references = app(ReferenceNumberService::class);

        $profiles = [
            'superadmin@hospital.test' => ['department' => 'ADM', 'position' => 'System Owner', 'role_title' => 'Super Admin', 'sex' => Sex::Female],
            'admin@hospital.test' => ['department' => 'ADM', 'position' => 'Hospital Administrator', 'role_title' => 'Hospital Admin', 'sex' => Sex::Male],
            'doctor@hospital.test' => ['department' => 'GEN', 'position' => 'Attending Physician', 'role_title' => 'Doctor', 'sex' => Sex::Male, 'specialization' => 'Internal Medicine', 'fee' => 750, 'room' => 'GEN-201', 'max' => 20],
            'nurse@hospital.test' => ['department' => 'ER', 'position' => 'Staff Nurse', 'role_title' => 'Nurse', 'sex' => Sex::Female],
            'pharmacist@hospital.test' => ['department' => 'PHA', 'position' => 'Pharmacist', 'role_title' => 'Pharmacist', 'sex' => Sex::Female],
            'laboratory@hospital.test' => ['department' => 'LAB', 'position' => 'Medical Technologist', 'role_title' => 'Laboratory Staff', 'sex' => Sex::Male],
            'cashier@hospital.test' => ['department' => 'BIL', 'position' => 'Cashier', 'role_title' => 'Cashier', 'sex' => Sex::Female],
        ];

        foreach ($profiles as $email => $profile) {
            $user = User::where('email', $email)->first();
            $department = Department::where('code', $profile['department'])->first();

            if (! $user || ! $department) {
                continue;
            }

            [$firstName, $lastName] = $this->splitName($user->name);

            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_number' => Employee::where('user_id', $user->id)->value('employee_number') ?? $references->employeeNumber(),
                    'department_id' => $department->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'date_of_birth' => '1990-01-01',
                    'sex' => $profile['sex'],
                    'civil_status' => 'Single',
                    'email' => $user->email,
                    'contact_number' => '+63 900 000 0000',
                    'position' => $profile['position'],
                    'employment_type' => EmploymentType::Regular,
                    'employment_status' => EmploymentStatus::Active,
                    'hire_date' => now('Asia/Manila')->subYears(2)->toDateString(),
                    'license_number' => in_array($profile['role_title'], ['Doctor', 'Nurse', 'Pharmacist', 'Laboratory Staff'], true) ? 'PRC-DEMO-'.strtoupper($profile['department']) : null,
                    'license_expiration_date' => now('Asia/Manila')->addYears(2)->toDateString(),
                    'specialization' => $profile['specialization'] ?? null,
                    'consultation_fee' => $profile['fee'] ?? null,
                    'maximum_appointments_per_day' => $profile['max'] ?? null,
                    'clinic_room' => $profile['room'] ?? null,
                    'work_schedule_notes' => 'Demo schedule notes. Final scheduling is planned for a later phase.',
                    'emergency_contact_name' => 'Demo Emergency Contact',
                    'emergency_contact_relationship' => 'Relative',
                    'emergency_contact_number' => '+63 900 000 0001',
                ]
            );
        }

        Department::where('code', 'ADM')->update(['department_head_employee_id' => Employee::where('email', 'admin@hospital.test')->value('id')]);
        Department::where('code', 'GEN')->update(['department_head_employee_id' => Employee::where('email', 'doctor@hospital.test')->value('id')]);
        Department::where('code', 'ER')->update(['department_head_employee_id' => Employee::where('email', 'nurse@hospital.test')->value('id')]);
        Department::where('code', 'LAB')->update(['department_head_employee_id' => Employee::where('email', 'laboratory@hospital.test')->value('id')]);
        Department::where('code', 'PHA')->update(['department_head_employee_id' => Employee::where('email', 'pharmacist@hospital.test')->value('id')]);
        Department::where('code', 'BIL')->update(['department_head_employee_id' => Employee::where('email', 'cashier@hospital.test')->value('id')]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = str_replace('Dr. ', '', $name);
        $parts = explode(' ', $name);

        return [$parts[0], $parts[array_key_last($parts)]];
    }
}
