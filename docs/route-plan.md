# Route Plan

## Current Route System

This project uses Laravel 12.64.0. Route registration currently lives in `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->create();
```

Additional route files can be registered in Laravel 12 by adding a `then` closure to `withRouting()` and requiring each file there, or by requiring role files from `routes/web.php`. The preferred plan is `bootstrap/app.php` registration so `routes/web.php` remains small.

## Planned Route Files

```text
routes/web.php
routes/admin.php
routes/doctor.php
routes/nurse.php
routes/patient.php
routes/pharmacist.php
routes/laboratory.php
routes/cashier.php
routes/api.php
```

## Route Group Plan

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('super-admin')
        ->name('super-admin.')
        ->middleware('role:super-admin')
        ->group(function () {
            Route::view('dashboard', 'dashboards.super-admin')->name('dashboard');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:super-admin|hospital-admin')
        ->group(function () {
            Route::view('dashboard', 'dashboards.admin')->name('dashboard');
        });

    Route::prefix('doctor')
        ->name('doctor.')
        ->middleware('role:doctor')
        ->group(function () {
            Route::view('dashboard', 'dashboards.doctor')->name('dashboard');
        });

    Route::prefix('nurse')
        ->name('nurse.')
        ->middleware('role:nurse')
        ->group(function () {
            Route::view('dashboard', 'dashboards.nurse')->name('dashboard');
        });

    Route::prefix('patient')
        ->name('patient.')
        ->middleware('role:patient')
        ->group(function () {
            Route::view('dashboard', 'dashboards.patient')->name('dashboard');
        });

    Route::prefix('pharmacist')
        ->name('pharmacist.')
        ->middleware('role:pharmacist')
        ->group(function () {
            Route::view('dashboard', 'dashboards.pharmacist')->name('dashboard');
        });

    Route::prefix('laboratory')
        ->name('laboratory.')
        ->middleware('role:laboratory-staff')
        ->group(function () {
            Route::view('dashboard', 'dashboards.laboratory')->name('dashboard');
        });

    Route::prefix('cashier')
        ->name('cashier.')
        ->middleware('role:cashier')
        ->group(function () {
            Route::view('dashboard', 'dashboards.cashier')->name('dashboard');
        });
});
```

## Dashboard Routes

| URI | Name | Role |
| --- | --- | --- |
| `/super-admin/dashboard` | `super-admin.dashboard` | Super Admin |
| `/admin/dashboard` | `admin.dashboard` | Hospital Admin and Super Admin |
| `/doctor/dashboard` | `doctor.dashboard` | Doctor |
| `/nurse/dashboard` | `nurse.dashboard` | Nurse |
| `/patient/dashboard` | `patient.dashboard` | Patient |
| `/pharmacist/dashboard` | `pharmacist.dashboard` | Pharmacist |
| `/laboratory/dashboard` | `laboratory.dashboard` | Laboratory Staff |
| `/cashier/dashboard` | `cashier.dashboard` | Cashier |

## Login Redirect Logic

After login, a role-aware redirect service or middleware should route users to their highest-priority dashboard:

1. `super-admin.dashboard`
2. `admin.dashboard`
3. `doctor.dashboard`
4. `nurse.dashboard`
5. `patient.dashboard`
6. `pharmacist.dashboard`
7. `laboratory.dashboard`
8. `cashier.dashboard`

Users with no valid active role should be redirected to a safe account page or logged out with a clear inactive-account message.
