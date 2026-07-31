<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\ReleaseReservationRequest;
use App\Models\PharmacyStockReservation;
use App\Services\Pharmacy\PharmacyInventoryService;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    public function release(ReleaseReservationRequest $request, PharmacyStockReservation $reservation, PharmacyInventoryService $inventory): RedirectResponse
    {
        $inventory->releaseReservation($reservation, $request->user(), $request->validated('reason'));

        return back()->with('status', 'Reservation released.');
    }
}
