@props(['title', 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 bg-white p-6 shadow-sm']) }}>
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-gray-950">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
        @endif
    </div>
    <div class="grid gap-5 md:grid-cols-2">
        {{ $slot }}
    </div>
</section>
