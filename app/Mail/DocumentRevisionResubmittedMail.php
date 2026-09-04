<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class DocumentRevisionResubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $updater;
    public $magicLoginUrl;

    public function __construct(Document $document, User $user, User $updater, $magicLoginUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        $this->updater = $updater;
        $this->magicLoginUrl = $magicLoginUrl ?? route('login');
    }

    public function build()
    {
        return $this->subject('🔄 [e-QMS] Dokumen Direvisi (Perlu Review Ulang) - ' . $this->document->title)
                    ->view('emails.document_revision_resubmitted');
    }
}
