<?php

namespace Tests\Feature\Admin;

use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestCategory;
use App\Models\SpecimenType;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_create_catalog_records(): void
    {
        $admin = $this->user('hospital-admin');

        $this->actingAs($admin)->post(route('admin.laboratory.categories.store'), ['code' => 'HEM', 'name' => 'Hematology'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.laboratory.specimen-types.store'), ['code' => 'BLD', 'name' => 'Blood'])->assertRedirect();

        $category = LaboratoryTestCategory::firstOrFail();
        $specimen = SpecimenType::firstOrFail();
        $this->actingAs($admin)->post(route('admin.laboratory.tests.store'), [
            'laboratory_test_category_id' => $category->id,
            'specimen_type_id' => $specimen->id,
            'code' => 'FBS',
            'name' => 'Fasting Blood Sugar',
            'result_type' => 'numeric',
            'default_unit' => 'mg/dL',
        ])->assertRedirect();

        $this->assertDatabaseHas('laboratory_tests', ['code' => 'FBS']);
    }

    public function test_category_code_is_unique_and_patient_is_forbidden(): void
    {
        $admin = $this->user('hospital-admin');
        LaboratoryTestCategory::factory()->create(['code' => 'HEM']);

        $this->actingAs($admin)->post(route('admin.laboratory.categories.store'), ['code' => 'HEM', 'name' => 'Other'])
            ->assertSessionHasErrors('code');

        $this->actingAs($this->user('patient'))->get(route('admin.laboratory.catalog.index'))->assertForbidden();
    }

    public function test_panel_expands_components_in_requests(): void
    {
        $admin = $this->user('hospital-admin');
        $category = LaboratoryTestCategory::factory()->create();
        $specimen = SpecimenType::factory()->create();
        $component = LaboratoryTest::factory()->create(['specimen_type_id' => $specimen->id]);

        $this->actingAs($admin)->post(route('admin.laboratory.tests.store'), [
            'laboratory_test_category_id' => $category->id,
            'specimen_type_id' => $specimen->id,
            'code' => 'CBC',
            'name' => 'Complete Blood Count',
            'result_type' => 'structured',
            'is_panel' => true,
            'component_test_ids' => [$component->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('laboratory_test_components', ['component_test_id' => $component->id]);
    }

    private function user(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
