@props(['id', 'title', 'message', 'action', 'method' => 'DELETE', 'button' => 'Confirm'])

<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" {{ $attributes->merge(['class' => 'text-sm font-medium text-red-700 hover:text-red-900']) }}>
        {{ $slot }}
    </button>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/40 p-4">
        <div @click.outside="open = false" class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-gray-950">{{ $title }}</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">{{ $message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <button class="rounded-md bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">{{ $button }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
