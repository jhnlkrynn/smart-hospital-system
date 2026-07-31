<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\UpdateOwnPatientProfileRequest;
use App\Services\Patient\PatientQrService;
use App\Services\Patient\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    public function show(Request $request): View
    {
        $patient = $request->user()->patient()->with(['emergencyContacts', 'allergies', 'conditions'])->firstOrFail();
        $this->authorize('view', $patient);

        return view('patient.profile.show', ['patient' => $patient]);
    }

    public function edit(Request $request): View
    {
        $patient = $request->user()->patient()->firstOrFail();
        $this->authorize('update', $patient);

        return view('patient.profile.edit', ['patient' => $patient]);
    }

    public function update(UpdateOwnPatientProfileRequest $request, PatientService $patients): RedirectResponse
    {
        $patients->updateOwnProfile($request->user()->patient, $request->validated());

        return redirect()->route('patient.profile.show')->with('status', 'Profile updated successfully.');
    }

    public function qrCard(Request $request, PatientQrService $qr): View
    {
        $patient = $request->user()->patient()->firstOrFail();
        $this->authorize('viewQr', $patient);

        return view('patient.profile.qr-card', ['patient' => $patient, 'qrImage' => $qr->generateQrImage($patient)]);
    }
}
