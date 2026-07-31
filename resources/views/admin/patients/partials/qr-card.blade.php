<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-6 print:flex-row sm:flex-row">
        @if($qrImage)
            <img src="{{ $qrImage }}" alt="Patient QR code" class="h-48 w-48 rounded-md border border-gray-200 bg-white p-2">
        @endif
        <div>
            <p class="text-sm font-semibold uppercase text-gray-500">Smart Hospital Patient Card</p>
            <h3 class="mt-2 text-2xl font-bold text-gray-950">{{ $patient->full_name }}</h3>
            <p class="mt-2 text-sm text-gray-600">Patient Number</p>
            <p class="text-lg font-semibold text-gray-950">{{ $patient->patient_number }}</p>
            <p class="mt-4 max-w-md text-sm leading-6 text-gray-600">Present this card to authorized hospital staff for patient identification. This is not a government-issued ID.</p>
            <button onclick="window.print()" class="mt-4 rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white print:hidden">Print Card</button>
        </div>
    </div>
</section>
