<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientPrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;
        abort_unless($patient, 403);

        return view('patient.prescriptions.index', [
            'prescriptions' => Prescription::query()->with(['doctor', 'items'])->where('patient_id', $patient->id)->latest()->paginate(20),
        ]);
    }

    public function show(Request $request, Prescription $prescription): View
    {
        abort_unless((int) $prescription->patient_id === (int) $request->user()->patient?->id, 403);

        return view('patient.prescriptions.show', ['prescription' => $prescription->load(['doctor', 'items.medication'])]);
    }
}
