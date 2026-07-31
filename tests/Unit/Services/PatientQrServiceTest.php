<?php

namespace Tests\Unit\Services;

use App\Models\Patient;
use App\Services\Patient\PatientQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientQrServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_image_contains_svg_data_uri_and_token_is_not_patient_data(): void
    {
        $patient = Patient::factory()->create(['first_name' => 'Secret']);
        $service = app(PatientQrService::class);

        $this->assertStringStartsWith('data:image/svg+xml', $service->generateQrImage($patient));
        $this->assertStringNotContainsString('Secret', $patient->getRawOriginal('qr_token'));
    }
}
