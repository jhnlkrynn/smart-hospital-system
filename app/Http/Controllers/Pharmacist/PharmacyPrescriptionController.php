<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\Pharmacy\PharmacyInventoryService;
use App\Services\Pharmacy\PrescriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyPrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        return view('pharmacist.prescriptions.index', [
            'prescriptions' => Prescription::query()->with(['patient', 'doctor', 'items'])->latest()->paginate(25),
        ]);
    }

    public function show(Prescription $prescription): View
    {
        return view('pharmacist.prescriptions.show', ['prescription' => $prescription->load(['patient.allergies', 'doctor', 'items.medication', 'allergyWarnings', 'reservations.stockBatch.location'])]);
    }

    public function review(Request $request, Prescription $prescription, PrescriptionService $service): RedirectResponse
    {
        $service->markReviewed($prescription, $request->user());

        return back()->with('status', 'Prescription reviewed.');
    }

    public function reserve(Request $request, Prescription $prescription, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->reserveForPrescription($prescription, $request->user());

        return back()->with('status', 'Stock reservation completed.');
    }
}
