<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use App\Services\Patient\PatientService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, RoleRedirectService $redirects, PatientService $patients): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'sex' => ['required', 'string', 'in:female,male,other'],
            'contact_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $patients): User {
            $user = User::create([
                'name' => trim($request->first_name.' '.$request->last_name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => UserStatus::Active,
            ]);

            Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
            $user->assignRole('patient');
            $patients->createFromRegistration($user, $request->only([
                'first_name', 'middle_name', 'last_name', 'date_of_birth', 'sex', 'contact_number',
            ]));

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect($redirects->redirectPathFor($user));
    }
}
