@props([
    'title',
    'description' => null,
    'status' => 'Upcoming module',
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-gray-950">{{ $title }}</h3>
            @if ($description)
                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $description }}</p>
            @endif
        </div>
        <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-200">
            {{ $status }}
        </span>
    </div>
</div>
