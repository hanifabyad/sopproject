<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\NewSopRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSopRequestCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public NewSopRequest $sopRequest;
    public Document $document;
    public User $recipient;
    public ?string $libraryUrl;

    public function __construct(NewSopRequest $sopRequest, Document $document, User $recipient, ?string $libraryUrl = null)
    {
        $this->sopRequest = $sopRequest;
        $this->document = $document;
        $this->recipient = $recipient;
        $this->libraryUrl = $libraryUrl;
    }

    public function build()
    {
        $docTitle = $this->document->title ?? $this->sopRequest->title;
        return $this->subject("[e-QMS] SOP Telah Terbit & Sah: {$docTitle}")
                    ->view('emails.new_sop_request_completed');
    }
}
