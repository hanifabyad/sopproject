<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class PeriodicSopQuizInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $quizUrl;

    public function __construct(Document $document, User $user, $quizUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        $this->quizUrl = $quizUrl ?? route('documents.quiz.show', $document->id);
    }

    public function build()
    {
        $docNo = $this->document->doc_number ?: 'SOP';
        return $this->subject("📝 [Uji Pemahaman SOP 6 Bulanan] {$docNo} - {$this->document->title}")
                    ->view('emails.periodic_sop_quiz_invitation');
    }
}
