<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_user_and_patient_profile(): void
    {
        $this->post('/register', [
            'first_name' => 'Public',
            'last_name' => 'Patient',
            'date_of_birth' => '2000-01-01',
            'sex' => 'female',
            'email' => 'public.patient@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'super-admin',
            'status' => 'archived',
        ])->assertRedirect(route('patient.dashboard', absolute: false));

        $user = User::where('email', 'public.patient@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('patient'));
        $patient = Patient::where('user_id', $user->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^PAT-\d{4}-\d{6}$/', $patient->patient_number);
        $this->assertSame('active', $patient->status->value);
    }
}
