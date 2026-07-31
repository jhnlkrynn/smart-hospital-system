<?php

namespace App\Http\Controllers\PatientLookup;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientLookupController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('patients.lookup-qr'), 403);

        return view('patient-lookup.index');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('patients.lookup-qr'), 403);
        $data = $request->validate(['token' => ['required', 'string', 'max:255']]);

        return redirect()->route('patient-lookup.show', ['token' => $data['token']]);
    }

    public function show(string $token, AuditLogService $auditLog): View
    {
        abort_unless(request()->user()->can('patients.lookup-qr'), 403);

        $patient = Patient::where('qr_token', $token)->first();
        $auditLog->record(request()->user(), $patient ? 'qr_lookup_success' : 'qr_lookup_failed', 'patients', $patient, 'Patient QR lookup attempted.', null, ['qr_token' => '[masked]'], request());

        abort_unless($patient, 404);

        return view('patient-lookup.result', ['patient' => $patient->load(['allergies'])]);
    }
}
