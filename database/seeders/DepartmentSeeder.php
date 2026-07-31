<?php

namespace Database\Seeders;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'ADM', 'name' => 'Administration'],
            ['code' => 'GEN', 'name' => 'General Medicine'],
            ['code' => 'PED', 'name' => 'Pediatrics'],
            ['code' => 'CAR', 'name' => 'Cardiology'],
            ['code' => 'DER', 'name' => 'Dermatology'],
            ['code' => 'ORT', 'name' => 'Orthopedics'],
            ['code' => 'OBG', 'name' => 'Obstetrics and Gynecology'],
            ['code' => 'ER', 'name' => 'Emergency Department'],
            ['code' => 'LAB', 'name' => 'Laboratory'],
            ['code' => 'PHA', 'name' => 'Pharmacy'],
            ['code' => 'BIL', 'name' => 'Billing'],
            ['code' => 'RAD', 'name' => 'Radiology'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                [
                    'name' => $department['name'],
                    'description' => $department['name'].' department for the demo hospital.',
                    'location' => $department['code'].' Wing',
                    'status' => DepartmentStatus::Active,
                ]
            );
        }
    }
}
