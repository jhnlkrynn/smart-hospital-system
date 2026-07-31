<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\AmendLaboratoryResultRequest;
use App\Http\Requests\Laboratory\CollectSpecimenRequest;
use App\Http\Requests\Laboratory\RejectSpecimenRequest;
use App\Http\Requests\Laboratory\StoreLaboratoryAttachmentRequest;
use App\Http\Requests\Laboratory\StoreLaboratoryResultRequest;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use App\Models\LaboratorySpecimen;
use App\Models\SpecimenType;
use App\Services\Laboratory\LaboratoryResultAttachmentService;
use App\Services\Laboratory\LaboratoryResultService;
use App\Services\Laboratory\SpecimenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaboratoryWorkQueueController extends Controller
{
    public function __construct(
        private readonly SpecimenService $specimens,
        private readonly LaboratoryResultService $results,
        private readonly LaboratoryResultAttachmentService $attachments,
    ) {}

    public function index(Request $request): View
    {
        return view('laboratory.requests.index', [
            'requests' => LaboratoryRequest::query()->with(['patient', 'doctor', 'items'])->search($request->string('search')->toString())->latest()->paginate(20),
        ]);
    }

    public function show(LaboratoryRequest $laboratoryRequest): View
    {
        return view('laboratory.requests.show', [
            'laboratoryRequest' => $laboratoryRequest->load(['patient', 'doctor', 'items.result', 'specimens.items', 'results']),
            'specimenTypes' => SpecimenType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function collect(CollectSpecimenRequest $request, LaboratoryRequest $laboratoryRequest): RedirectResponse
    {
        $this->specimens->collect($laboratoryRequest, $request->validated(), $request->user());

        return back()->with('status', 'Specimen collected.');
    }

    public function accept(Request $request, LaboratorySpecimen $specimen): RedirectResponse
    {
        abort_unless($request->user()->can('laboratory-requests.collect-specimen'), 403);
        $this->specimens->accept($specimen, $request->user());

        return back()->with('status', 'Specimen accepted.');
    }

    public function reject(RejectSpecimenRequest $request, LaboratorySpecimen $specimen): RedirectResponse
    {
        $this->specimens->reject($specimen, $request->validated('reason'), $request->user());

        return back()->with('status', 'Specimen rejected.');
    }

    public function enterResult(StoreLaboratoryResultRequest $request, LaboratoryRequestItem $item): RedirectResponse
    {
        $this->results->enter($item, $request->validated(), $request->user());

        return back()->with('status', 'Result entered.');
    }

    public function verify(Request $request, LaboratoryResult $result): RedirectResponse
    {
        abort_unless($request->user()->can('laboratory-results.verify'), 403);
        $this->results->verify($result, $request->user(), $request->input('notes'));

        return back()->with('status', 'Result verified.');
    }

    public function release(Request $request, LaboratoryResult $result): RedirectResponse
    {
        abort_unless($request->user()->can('laboratory-results.release'), 403);
        $this->results->release($result, $request->user());

        return back()->with('status', 'Result released.');
    }

    public function amend(AmendLaboratoryResultRequest $request, LaboratoryResult $result): RedirectResponse
    {
        $this->results->amend($result, $request->validated(), $request->user());

        return back()->with('status', 'Result amended.');
    }

    public function uploadAttachment(StoreLaboratoryAttachmentRequest $request, LaboratoryResult $result): RedirectResponse
    {
        $this->attachments->upload($result, $request->file('file'), $request->validated(), $request->user());

        return back()->with('status', 'Attachment uploaded.');
    }

    public function downloadAttachment(LaboratoryResult $result, int $attachment): StreamedResponse
    {
        $attachment = $result->attachments()->findOrFail($attachment);
        abort_unless(Storage::disk($attachment->storage_disk)->exists($attachment->storage_path), 404);

        return Storage::disk($attachment->storage_disk)->download($attachment->storage_path, $attachment->original_filename);
    }
}
