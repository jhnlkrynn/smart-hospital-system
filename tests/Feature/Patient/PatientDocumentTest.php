<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PatientDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_executable_patient_document_is_rejected(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $patient = Patient::factory()->create();

        $this->actingAs($admin)->post(route('admin.patients.documents.store', $patient), [
            'document_type' => 'identification',
            'title' => 'Unsafe',
            'document' => UploadedFile::fake()->create('payload.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('document');
    }
}
