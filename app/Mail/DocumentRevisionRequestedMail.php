<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class DocumentRevisionRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $requester;
    public $notes;
    public $magicLoginUrl;
    public $isCreator;

    public function __construct(Document $document, User $user, User $requester, $notes, $magicLoginUrl = null, bool $isCreator = true)
    {
        $this->document = $document;
        $this->user = $user;
        $this->requester = $requester;
        $this->notes = $notes ?? 'Dokumen memerlukan perbaikan.';
        $this->magicLoginUrl = $magicLoginUrl ?? route('login');
        $this->isCreator = $isCreator;
    }

    public function build()
    {
        $subjectPrefix = $this->isCreator ? '[e-QMS] Dokumen Memerlukan Revisi' : '[e-QMS] Pemberitahuan Dokumen Terkunci Revisi';
        return $this->subject($subjectPrefix . ' - ' . $this->document->title)
                    ->view('emails.document_revision_requested');
    }
}
