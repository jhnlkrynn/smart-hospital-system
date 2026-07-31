<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\ReceivePurchaseRequest;
use App\Http\Requests\Pharmacy\StorePurchaseRequest;
use App\Models\Medication;
use App\Models\PharmacyLocation;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySupplier;
use App\Services\Pharmacy\PharmacyInventoryService;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return view('pharmacist.purchases.index', ['purchases' => PharmacyPurchase::query()->with('supplier')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('pharmacist.purchases.create', [
            'suppliers' => PharmacySupplier::query()->orderBy('name')->get(),
            'medications' => Medication::query()->orderBy('generic_name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request, ReferenceNumberService $numbers): RedirectResponse
    {
        $purchase = PharmacyPurchase::create($request->safe()->except('items') + [
            'purchase_number' => $numbers->pharmacyPurchaseNumber(),
            'status' => 'ordered',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        foreach ($request->validated('items') as $item) {
            $purchase->items()->create($item);
        }

        return redirect()->route('pharmacist.purchases.show', $purchase)->with('status', 'Purchase order created.');
    }

    public function show(PharmacyPurchase $purchase): View
    {
        return view('pharmacist.purchases.show', [
            'purchase' => $purchase->load(['supplier', 'items.medication']),
            'locations' => PharmacyLocation::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function receive(ReceivePurchaseRequest $request, PharmacyPurchase $purchase, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->receivePurchase($purchase, $request->validated('received_items'), (int) $request->validated('pharmacy_location_id'), $request->user());

        return back()->with('status', 'Purchase receipt posted to inventory.');
    }
}
