@props(['title', 'message'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center']) }}>
    <h3 class="text-base font-semibold text-gray-950">{{ $title }}</h3>
    <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
</div>
