<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreMedicationRequest;
use App\Models\DosageForm;
use App\Models\Medication;
use App\Models\MedicationCategory;
use App\Models\MedicationFrequency;
use App\Models\MedicationManufacturer;
use App\Models\MedicationRoute;
use App\Models\MedicationUnit;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationCatalogController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.medications.catalog.index', [
            'medications' => Medication::query()
                ->with(['category', 'dosageForm', 'strengthUnit'])
                ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('generic_name', 'like', "%{$search}%")->orWhere('brand_name', 'like', "%{$search}%")->orWhere('medication_number', 'like', "%{$search}%"))
                ->orderBy('generic_name')
                ->paginate(25),
            'categories' => MedicationCategory::query()->orderBy('name')->get(),
            'forms' => DosageForm::query()->orderBy('name')->get(),
            'units' => MedicationUnit::query()->orderBy('name')->get(),
            'routes' => MedicationRoute::query()->orderBy('name')->get(),
            'frequencies' => MedicationFrequency::query()->orderBy('name')->get(),
            'manufacturers' => MedicationManufacturer::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMedicationRequest $request, ReferenceNumberService $numbers): RedirectResponse
    {
        Medication::create($request->validated() + [
            'medication_number' => $numbers->medicationNumber(),
            'requires_prescription' => $request->boolean('requires_prescription', true),
            'is_controlled' => $request->boolean('is_controlled'),
            'is_high_alert' => $request->boolean('is_high_alert'),
            'requires_cold_storage' => $request->boolean('requires_cold_storage'),
            'is_active' => true,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Medication added to the catalog.');
    }

    public function destroy(Medication $medication): RedirectResponse
    {
        $medication->delete();

        return back()->with('status', 'Medication archived.');
    }
}
