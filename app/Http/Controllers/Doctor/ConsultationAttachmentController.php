<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreConsultationAttachmentRequest;
use App\Models\Consultation;
use App\Models\ConsultationAttachment;
use App\Services\Consultation\ConsultationAttachmentService;
use App\Services\Consultation\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationAttachmentController extends Controller
{
    public function __construct(
        private readonly ConsultationAttachmentService $attachments,
        private readonly ConsultationService $consultations,
    ) {}

    public function store(StoreConsultationAttachmentRequest $request, Consultation $consultation): RedirectResponse
    {
        $this->consultations->ensureDoctorOwns($consultation, $request->user());
        $this->attachments->upload($consultation, $request->file('file'), $request->validated(), $request->user());

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(Consultation $consultation, ConsultationAttachment $attachment): StreamedResponse
    {
        abort_unless((int) $attachment->consultation_id === (int) $consultation->id, 404);
        abort_unless(Storage::disk($attachment->storage_disk)->exists($attachment->storage_path), 404);

        return Storage::disk($attachment->storage_disk)->download($attachment->storage_path, $attachment->original_filename);
    }
}
