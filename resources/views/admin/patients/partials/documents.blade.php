<section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-950">Private Documents</h3>
    <div class="mt-4 grid gap-3">
        @forelse($patient->documents as $document)
            <div class="flex items-center justify-between rounded-md border border-gray-200 p-4 text-sm">
                <div><span class="font-semibold text-gray-950">{{ $document->title }}</span><span class="block text-gray-600">{{ $document->original_filename }}</span></div>
                @can('patients.download-documents')<a class="font-medium text-blue-700" href="{{ route('admin.patients.documents.download', [$patient, $document]) }}">Download</a>@endcan
            </div>
        @empty
            <p class="text-sm text-gray-600">No documents uploaded.</p>
        @endforelse
    </div>
    @can('patients.manage-documents')
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.patients.documents.store', $patient) }}" class="mt-5 grid gap-3 md:grid-cols-4">
            @csrf
            <input name="title" placeholder="Document title" class="rounded-md border-gray-300">
            <select name="document_type" class="rounded-md border-gray-300">@foreach($documentTypes as $type)<option value="{{ $type->value }}">{{ str($type->value)->replace('_', ' ')->title() }}</option>@endforeach</select>
            <input name="document" type="file" class="text-sm">
            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Upload</button>
        </form>
    @endcan
</section>
