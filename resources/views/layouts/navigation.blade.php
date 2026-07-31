@php
    $user = Auth::user();
    $roleLabel = $user?->roles->first()?->name
        ? \Illuminate\Support\Str::of($user->roles->first()->name)->replace('-', ' ')->title()
        : 'Role pending';

    $dashboardLinks = [
        ['permission' => 'dashboard.super-admin', 'route' => 'super-admin.dashboard', 'label' => 'Super Admin'],
        ['permission' => 'dashboard.hospital-admin', 'route' => 'admin.dashboard', 'label' => 'Admin'],
        ['permission' => 'dashboard.doctor', 'route' => 'doctor.dashboard', 'label' => 'Doctor'],
        ['permission' => 'dashboard.nurse', 'route' => 'nurse.dashboard', 'label' => 'Nurse'],
        ['permission' => 'dashboard.patient', 'route' => 'patient.dashboard', 'label' => 'Patient'],
        ['permission' => 'dashboard.pharmacist', 'route' => 'pharmacist.dashboard', 'label' => 'Pharmacy'],
        ['permission' => 'dashboard.laboratory', 'route' => 'laboratory.dashboard', 'label' => 'Laboratory'],
        ['permission' => 'dashboard.cashier', 'route' => 'cashier.dashboard', 'label' => 'Cashier'],
    ];

    $adminLinks = [
        ['permission' => 'departments.view', 'route' => 'admin.departments.index', 'label' => 'Departments'],
        ['permission' => 'employees.view', 'route' => 'admin.employees.index', 'label' => 'Employees'],
        ['permission' => 'patients.view', 'route' => 'admin.patients.index', 'label' => 'Patients'],
        ['permission' => 'patients.lookup-qr', 'route' => 'patient-lookup.index', 'label' => 'Patient QR Lookup'],
    ];

    $patientLinks = [
        ['permission' => 'patients.view-own-record', 'route' => 'patient.profile.show', 'label' => 'My Patient Profile'],
        ['permission' => 'patients.view-qr', 'route' => 'patient.qr-card', 'label' => 'My QR Card'],
    ];
@endphp

<nav x-data="{ open: false }" class="border-b border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-900" />
                    <span class="hidden text-sm font-semibold text-gray-950 md:inline">Smart Hospital</span>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    @foreach ($dashboardLinks as $link)
                        @can($link['permission'])
                            <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])">
                                {{ $link['label'] }}
                            </x-nav-link>
                        @endcan
                    @endforeach
                    @foreach ($adminLinks as $link)
                        @can($link['permission'])
                            <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'].'*')">
                                {{ $link['label'] }}
                            </x-nav-link>
                        @endcan
                    @endforeach
                    @foreach ($patientLinks as $link)
                        @can($link['permission'])
                            @if (Route::has($link['route']))
                                <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])">
                                    {{ $link['label'] }}
                                </x-nav-link>
                            @endif
                        @endcan
                    @endforeach
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-950">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500">{{ $roleLabel }}</div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900 focus:outline-none">
                            Account
                            <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        @if ($user->employee)
                            <x-dropdown-link :href="route('profile.employment')">{{ __('Employment Profile') }}</x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-gray-200 sm:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($dashboardLinks as $link)
                @can($link['permission'])
                    <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])">
                        {{ $link['label'] }}
                    </x-responsive-nav-link>
                @endcan
            @endforeach
            @foreach ($adminLinks as $link)
                @can($link['permission'])
                    <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'].'*')">
                        {{ $link['label'] }}
                    </x-responsive-nav-link>
                @endcan
            @endforeach
            @foreach ($patientLinks as $link)
                @can($link['permission'])
                    @if (Route::has($link['route']))
                        <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])">
                            {{ $link['label'] }}
                        </x-responsive-nav-link>
                    @endif
                @endcan
            @endforeach
        </div>

        <div class="border-t border-gray-200 px-4 py-3">
            <div class="font-medium text-gray-950">{{ $user->name }}</div>
            <div class="text-sm text-gray-500">{{ $user->email }} · {{ $roleLabel }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                @if ($user->employee)
                    <x-responsive-nav-link :href="route('profile.employment')">{{ __('Employment Profile') }}</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
