<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class DocumentEvaluationDueMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $magicLoginUrl;

    public function __construct(Document $document, User $user, $magicLoginUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        $this->magicLoginUrl = $magicLoginUrl ?? route('login');
    }

    public function build()
    {
        return $this->subject('📋 Evaluasi SOP Diperlukan - e-QMS')
                    ->view('emails.document_evaluation_due');
    }
}
