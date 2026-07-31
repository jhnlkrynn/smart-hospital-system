<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_full_name_and_age_accessors(): void
    {
        $employee = Employee::factory()->make([
            'first_name' => 'Maria',
            'middle_name' => 'Luna',
            'last_name' => 'Santos',
            'suffix' => 'MD',
            'date_of_birth' => now()->subYears(30)->toDateString(),
        ]);

        $this->assertSame('Maria Luna Santos MD', $employee->full_name);
        $this->assertSame(30, $employee->age);
    }
}
