@props(['active' => false])

<a {{ $attributes->merge([
    'class' => $active
        ? 'block rounded-md bg-gray-950 px-3 py-2 text-sm font-medium text-white'
        : 'block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-950',
]) }}>
    {{ $slot }}
</a>
