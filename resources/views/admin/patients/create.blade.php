<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-gray-950">Register Patient</h2></x-slot>
    <div class="py-8">
        <form method="POST" action="{{ route('admin.patients.store') }}" enctype="multipart/form-data" class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('admin.patients._form')
        </form>
    </div>
</x-app-layout>
