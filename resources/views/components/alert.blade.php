@props(['type' => 'success', 'message' => null])

@php
    $classes = $type === 'error'
        ? 'border-red-200 bg-red-50 text-red-800'
        : 'border-emerald-200 bg-emerald-50 text-emerald-800';
@endphp

@if ($message)
    <div {{ $attributes->merge(['class' => "rounded-md border px-4 py-3 text-sm {$classes}"]) }}>
        {{ $message }}
    </div>
@endif
