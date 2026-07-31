<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\AdjustStockRequest;
use App\Http\Requests\Pharmacy\QuarantineStockRequest;
use App\Models\MedicationStockBatch;
use App\Models\PharmacyInventoryTransaction;
use App\Services\Pharmacy\PharmacyInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('pharmacist.inventory.index', [
            'batches' => MedicationStockBatch::query()->with(['medication', 'location'])->latest()->paginate(25),
            'transactions' => PharmacyInventoryTransaction::query()->with(['medication', 'stockBatch'])->latest('occurred_at')->limit(20)->get(),
        ]);
    }

    public function adjust(AdjustStockRequest $request, MedicationStockBatch $batch, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->adjust($batch, $request->validated('adjustment_type'), (float) $request->validated('quantity'), $request->validated('reason'), $request->user());

        return back()->with('status', 'Stock adjustment recorded.');
    }

    public function quarantine(QuarantineStockRequest $request, MedicationStockBatch $batch, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->quarantine($batch, $request->user(), $request->validated('reason'));

        return back()->with('status', 'Batch quarantined.');
    }

    public function unquarantine(MedicationStockBatch $batch, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->unquarantine($batch, request()->user());

        return back()->with('status', 'Batch released from quarantine.');
    }
}
