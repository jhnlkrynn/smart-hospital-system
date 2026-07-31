<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreDiagnosisCatalogRequest;
use App\Models\DiagnosisCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosisCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $items = DiagnosisCatalog::query()
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.diagnosis-catalog.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.diagnosis-catalog.create', ['diagnosis' => new DiagnosisCatalog()]);
    }

    public function store(StoreDiagnosisCatalogRequest $request): RedirectResponse
    {
        DiagnosisCatalog::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
            'is_patient_visible_default' => $request->boolean('is_patient_visible_default', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.diagnosis-catalog.index')->with('status', 'Diagnosis catalog item created.');
    }

    public function edit(DiagnosisCatalog $diagnosisCatalog): View
    {
        return view('admin.diagnosis-catalog.edit', ['diagnosis' => $diagnosisCatalog]);
    }

    public function update(StoreDiagnosisCatalogRequest $request, DiagnosisCatalog $diagnosisCatalog): RedirectResponse
    {
        $diagnosisCatalog->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
            'is_patient_visible_default' => $request->boolean('is_patient_visible_default'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.diagnosis-catalog.index')->with('status', 'Diagnosis catalog item updated.');
    }

    public function destroy(DiagnosisCatalog $diagnosisCatalog): RedirectResponse
    {
        $diagnosisCatalog->delete();

        return back()->with('status', 'Diagnosis catalog item archived.');
    }
}
