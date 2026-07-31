@props(['status' => 'active'])

@php
    $color = match ($status) {
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'inactive' => 'bg-gray-100 text-gray-700 ring-gray-200',
        'suspended', 'locked' => 'bg-red-50 text-red-700 ring-red-200',
        default => 'bg-sky-50 text-sky-700 ring-sky-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {$color}"]) }}>
    {{ str($status)->replace('_', ' ')->title() }}
</span>
