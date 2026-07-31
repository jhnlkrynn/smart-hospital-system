<?php

namespace Database\Seeders;

use App\Enums\AllergySeverity;
use App\Enums\AllergyType;
use App\Enums\PatientConditionStatus;
use App\Enums\PatientStatus;
use App\Enums\Sex;
use App\Models\Patient;
use App\Models\User;
use App\Services\Patient\PatientQrService;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $references = app(ReferenceNumberService::class);
        $qr = app(PatientQrService::class);

        $patients = [
            ['Paolo', null, 'Mendoza', 'patient@hospital.test', Sex::Male],
            ['Jasmine', null, 'Torres', null, Sex::Female],
            ['Carlo', null, 'Bautista', null, Sex::Male],
            ['Andrea', null, 'Ramos', null, Sex::Female],
            ['Nathaniel', null, 'Lim', null, Sex::Male],
            ['Sofia', null, 'Castillo', null, Sex::Female],
        ];

        foreach ($patients as [$first, $middle, $last, $email, $sex]) {
            $user = $email ? User::where('email', $email)->first() : null;
            $patient = Patient::updateOrCreate(
                ['email' => $email ?? strtolower($first.'.'.$last).'@example.test'],
                [
                    'patient_number' => Patient::where('email', $email ?? strtolower($first.'.'.$last).'@example.test')->value('patient_number') ?? $references->patientNumber(),
                    'qr_token' => Patient::where('email', $email ?? strtolower($first.'.'.$last).'@example.test')->value('qr_token') ?? $qr->generateToken(),
                    'user_id' => $user?->id,
                    'first_name' => $first,
                    'middle_name' => $middle,
                    'last_name' => $last,
                    'date_of_birth' => '1995-01-01',
                    'sex' => $sex,
                    'civil_status' => 'Single',
                    'contact_number' => '+63 900 111 2222',
                    'registration_date' => now('Asia/Manila')->toDateString(),
                    'status' => PatientStatus::Active,
                ]
            );

            $patient->emergencyContacts()->updateOrCreate(
                ['name' => $first.' Emergency Contact'],
                ['relationship' => 'Relative', 'contact_number' => '+63 900 222 3333', 'is_primary' => true]
            );
            $patient->allergies()->updateOrCreate(
                ['allergen' => 'Penicillin'],
                ['allergy_type' => AllergyType::Medicine, 'severity' => AllergySeverity::Severe, 'reaction' => 'Rash', 'is_active' => true]
            );
            $patient->conditions()->updateOrCreate(
                ['condition_name' => 'Hypertension'],
                ['status' => PatientConditionStatus::Managed, 'notes' => 'Fictional demo condition.']
            );
        }
    }
}
