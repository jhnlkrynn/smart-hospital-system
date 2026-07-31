<?php

namespace Tests\Feature\Laboratory;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\LaboratoryTestItemStatus;
use App\Enums\SpecimenStatus;
use App\Models\LaboratoryReferenceRange;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use App\Models\LaboratorySpecimen;
use App\Models\LaboratoryTest;
use App\Models\SpecimenType;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecimenAndResultWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_laboratory_staff_collects_accepts_enters_verifies_and_releases_result(): void
    {
        $staff = $this->user('laboratory-staff');
        $specimenType = SpecimenType::factory()->create();
        $test = LaboratoryTest::factory()->create(['specimen_type_id' => $specimenType->id, 'default_unit' => 'mg/dL']);
        LaboratoryReferenceRange::factory()->create(['laboratory_test_id' => $test->id, 'lower_bound' => 70, 'upper_bound' => 100, 'critical_upper_bound' => 400]);
        $request = LaboratoryRequest::factory()->create(['status' => LaboratoryRequestStatus::SpecimenPending]);
        $item = LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $request->id, 'laboratory_test_id' => $test->id, 'specimen_type_id' => $specimenType->id, 'test_code_snapshot' => $test->code, 'test_name_snapshot' => $test->name, 'unit_snapshot' => 'mg/dL']);

        $this->actingAs($staff)->post(route('laboratory.requests.specimens.collect', $request), ['specimen_type_id' => $specimenType->id, 'item_ids' => [$item->id]])->assertRedirect();
        $specimen = LaboratorySpecimen::firstOrFail();
        $this->assertDatabaseHas('laboratory_specimens', ['id' => $specimen->id, 'status' => SpecimenStatus::Collected->value]);

        $this->actingAs($staff)->post(route('laboratory.specimens.accept', $specimen))->assertRedirect();
        $this->actingAs($staff)->post(route('laboratory.items.results.store', $item), ['numeric_value' => 120, 'unit' => 'mg/dL'])->assertRedirect();
        $result = LaboratoryResult::firstOrFail();
        $this->assertDatabaseHas('laboratory_results', ['id' => $result->id, 'abnormal_flag' => 'high']);

        $this->actingAs($staff)->post(route('laboratory.results.verify', $result))->assertRedirect();
        $this->actingAs($staff)->post(route('laboratory.results.release', $result))->assertRedirect();

        $this->assertDatabaseHas('laboratory_request_items', ['id' => $item->id, 'status' => LaboratoryTestItemStatus::Released->value]);
        $this->assertDatabaseHas('laboratory_requests', ['id' => $request->id, 'status' => LaboratoryRequestStatus::Released->value]);
    }

    public function test_rejection_requires_reason_and_marks_recollection(): void
    {
        $staff = $this->user('laboratory-staff');
        $specimen = LaboratorySpecimen::factory()->create();

        $this->actingAs($staff)->post(route('laboratory.specimens.reject', $specimen), ['reason' => 'Bad'])->assertSessionHasErrors('reason');
        $this->actingAs($staff)->post(route('laboratory.specimens.reject', $specimen), ['reason' => 'Hemolyzed specimen requires recollection.'])->assertRedirect();

        $this->assertDatabaseHas('laboratory_specimens', ['id' => $specimen->id, 'status' => SpecimenStatus::Rejected->value]);
    }

    private function user(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
