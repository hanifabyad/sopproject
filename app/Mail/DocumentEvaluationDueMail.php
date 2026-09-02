<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Models\User;

class DocumentEvaluationDueMail extends Mailable
{
    use Queueable, SerializesModels;

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
