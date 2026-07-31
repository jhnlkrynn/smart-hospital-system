<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DemoUserSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            PatientSeeder::class,
            AppointmentTypeSeeder::class,
            DoctorScheduleSeeder::class,
            DoctorScheduleExceptionSeeder::class,
            AppointmentSeeder::class,
            QueueSeeder::class,
            TriageSeeder::class,
            DiagnosisCatalogSeeder::class,
            ConsultationSeeder::class,
            SpecimenTypeSeeder::class,
            LaboratoryTestCategorySeeder::class,
            LaboratoryTestSeeder::class,
            LaboratoryReferenceRangeSeeder::class,
            MedicationCategorySeeder::class,
            DosageFormSeeder::class,
            MedicationRouteSeeder::class,
            MedicationUnitSeeder::class,
            MedicationFrequencySeeder::class,
            MedicationManufacturerSeeder::class,
            PharmacySupplierSeeder::class,
            MedicationSeeder::class,
            PharmacyLocationSeeder::class,
            PharmacyInventorySeeder::class,
            PharmacyPurchaseSeeder::class,
            PrescriptionSeeder::class,
            StockCountSeeder::class,
        ]);
    }
}
