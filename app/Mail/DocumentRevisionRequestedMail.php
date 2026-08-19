<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Models\User;

class DocumentRevisionRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $user;
    public $requester;
    public $notes;
    public $magicLoginUrl;

    public function __construct(Document $document, User $user, User $requester, $notes, $magicLoginUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        $this->requester = $requester;
        $this->notes = $notes ?? 'Dokumen memerlukan perbaikan.';
        $this->magicLoginUrl = $magicLoginUrl ?? route('login');
    }

    public function build()
    {
        return $this->subject('⚠️ [e-QMS] Dokumen Memerlukan Revisi - ' . $this->document->title)
                    ->view('emails.document_revision_requested');
    }
}
