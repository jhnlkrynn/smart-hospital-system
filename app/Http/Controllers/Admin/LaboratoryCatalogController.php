<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\StoreLaboratoryCategoryRequest;
use App\Http\Requests\Laboratory\StoreLaboratoryTestRequest;
use App\Http\Requests\Laboratory\StoreReferenceRangeRequest;
use App\Http\Requests\Laboratory\StoreSpecimenTypeRequest;
use App\Models\LaboratoryReferenceRange;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestCategory;
use App\Models\SpecimenType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LaboratoryCatalogController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.laboratory.catalog.index', [
            'categories' => LaboratoryTestCategory::query()->orderBy('display_order')->paginate(20, ['*'], 'categories'),
            'specimenTypes' => SpecimenType::query()->orderBy('name')->paginate(20, ['*'], 'specimens'),
            'tests' => LaboratoryTest::query()->with(['category', 'specimenType'])->when($request->string('search')->toString(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))->orderBy('name')->paginate(25, ['*'], 'tests'),
        ]);
    }

    public function storeCategory(StoreLaboratoryCategoryRequest $request): RedirectResponse
    {
        LaboratoryTestCategory::create($request->validated() + ['is_active' => $request->boolean('is_active', true), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return back()->with('status', 'Laboratory category created.');
    }

    public function storeSpecimenType(StoreSpecimenTypeRequest $request): RedirectResponse
    {
        SpecimenType::create($request->validated() + ['is_active' => $request->boolean('is_active', true), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return back()->with('status', 'Specimen type created.');
    }

    public function storeTest(StoreLaboratoryTestRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $test = LaboratoryTest::create($request->safe()->except('component_test_ids') + [
                'requires_fasting' => $request->boolean('requires_fasting'),
                'requires_verification' => $request->boolean('requires_verification', true),
                'is_panel' => $request->boolean('is_panel'),
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $this->syncComponents($test, $request->input('component_test_ids', []));
        });

        return back()->with('status', 'Laboratory test created.');
    }

    public function addReferenceRange(StoreReferenceRangeRequest $request, LaboratoryTest $test): RedirectResponse
    {
        $data = $request->validated();
        $overlap = $test->referenceRanges()
            ->where('is_active', true)
            ->where('sex', $data['sex'] ?? null)
            ->where('minimum_age_days', $data['minimum_age_days'] ?? null)
            ->where('maximum_age_days', $data['maximum_age_days'] ?? null)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['reference_range' => 'An active reference range already exists for the same demographic criteria.']);
        }

        LaboratoryReferenceRange::create($data + [
            'laboratory_test_id' => $test->id,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Reference range created.');
    }

    private function syncComponents(LaboratoryTest $test, array $componentIds): void
    {
        if (! $test->is_panel) {
            return;
        }

        if (in_array($test->id, $componentIds)) {
            throw ValidationException::withMessages(['component_test_ids' => 'A panel cannot contain itself.']);
        }

        $sync = [];
        foreach (array_values(array_unique($componentIds)) as $index => $id) {
            $sync[$id] = ['display_order' => $index + 1, 'is_required' => true];
        }
        $test->components()->sync($sync);
    }
}
