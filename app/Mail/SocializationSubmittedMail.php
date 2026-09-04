<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\DocumentSocialization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SocializationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public Document $document;
    public DocumentSocialization $socialization;
    public ?User $submitter;
    public ?string $actionUrl;

    public function __construct(Document $document, DocumentSocialization $socialization, ?User $submitter, ?string $actionUrl = null)
    {
        $this->document = $document;
        $this->socialization = $socialization;
        $this->submitter = $submitter;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject("[e-QMS] Bukti Sosialisasi SOP Diunggah: {$this->document->title}")
                    ->view('emails.socialization_submitted');
    }
}
