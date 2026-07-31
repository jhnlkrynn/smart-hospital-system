<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_full_name_and_age_accessors(): void
    {
        $patient = Patient::factory()->make([
            'first_name' => 'Ana',
            'middle_name' => 'Luz',
            'last_name' => 'Reyes',
            'date_of_birth' => now()->subYears(25)->toDateString(),
        ]);

        $this->assertSame('Ana Luz Reyes', $patient->full_name);
        $this->assertSame(25, $patient->age);
    }
}
