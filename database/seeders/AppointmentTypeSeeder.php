<?php

namespace Database\Seeders;

use App\Models\AppointmentType;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'CONSULT', 'name' => 'General Consultation', 'default_duration_minutes' => 30],
            ['code' => 'FOLLOWUP', 'name' => 'Follow-up Consultation', 'default_duration_minutes' => 20],
            ['code' => 'CHECKUP', 'name' => 'Routine Checkup', 'default_duration_minutes' => 30, 'requires_approval' => false],
            ['code' => 'SPECIALIST', 'name' => 'Specialist Consultation', 'default_duration_minutes' => 45],
            ['code' => 'PRENATAL', 'name' => 'Prenatal Consultation', 'default_duration_minutes' => 30],
            ['code' => 'PEDIATRIC', 'name' => 'Pediatric Consultation', 'default_duration_minutes' => 30],
        ];

        foreach ($types as $type) {
            AppointmentType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => 'Fictional demo appointment type.',
                    'default_duration_minutes' => $type['default_duration_minutes'],
                    'requires_approval' => $type['requires_approval'] ?? true,
                    'is_active' => true,
                ]
            );
        }
    }
}
