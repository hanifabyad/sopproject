<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\RevisionRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RevisionRequestSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public Document $document;
    public RevisionRequest $revisionRequest;
    public ?User $submitter;
    public ?string $actionUrl;

    public function __construct(Document $document, RevisionRequest $revisionRequest, ?User $submitter, ?string $actionUrl = null)
    {
        $this->document = $document;
        $this->revisionRequest = $revisionRequest;
        $this->submitter = $submitter;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject("[e-QMS] Permohonan Revisi SOP Masuk: {$this->document->title}")
                    ->view('emails.revision_request_submitted');
    }
}
