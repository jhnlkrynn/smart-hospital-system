<?php

namespace Tests\Unit\Services;

use App\Services\ReferenceNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_number_format_and_uniqueness(): void
    {
        $service = app(ReferenceNumberService::class);

        $first = $service->employeeNumber();
        $second = $service->employeeNumber();

        $this->assertMatchesRegularExpression('/^EMP-\d{4}-\d{6}$/', $first);
        $this->assertNotSame($first, $second);
    }
}
