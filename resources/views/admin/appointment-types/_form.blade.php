@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-gray-700">Code</label>
        <input name="code" value="{{ old('code', $type->code) }}" class="mt-1 w-full rounded-md border-gray-300">
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Name</label>
        <input name="name" value="{{ old('name', $type->name) }}" class="mt-1 w-full rounded-md border-gray-300">
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div>
        <label class="text-sm font-medium text-gray-700">Default duration</label>
        <input type="number" name="default_duration_minutes" value="{{ old('default_duration_minutes', $type->default_duration_minutes ?? 30) }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>
    <div class="flex items-center gap-6 pt-7">
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $type->requires_approval ?? true))> Requires approval</label>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active ?? true))> Active</label>
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $type->description) }}</textarea>
    </div>
</div>
<div class="mt-6"><button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Save</button></div>
