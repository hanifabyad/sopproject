<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\RevisionRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RevisionRequestApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public Document $document;
    public RevisionRequest $revisionRequest;
    public User $recipient;
    public string $uploadUrl;

    public function __construct(Document $document, RevisionRequest $revisionRequest, User $recipient, string $uploadUrl)
    {
        $this->document = $document;
        $this->revisionRequest = $revisionRequest;
        $this->recipient = $recipient;
        $this->uploadUrl = $uploadUrl;
    }

    public function build()
    {
        return $this->subject("[e-QMS] Permohonan Revisi Disetujui: {$this->document->title}")
                    ->view('emails.revision_request_approved');
    }
}
