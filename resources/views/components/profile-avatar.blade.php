@props(['employee', 'size' => 'h-12 w-12'])

<img
    src="{{ $employee->profile_photo_url }}"
    alt="{{ $employee->full_name }}"
    {{ $attributes->merge(['class' => "{$size} rounded-full object-cover ring-1 ring-gray-200"]) }}
>
