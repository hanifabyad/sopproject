<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public Document $document;
    public Evaluation $evaluation;
    public ?User $evaluator;
    public ?string $actionUrl;

    public function __construct(Document $document, Evaluation $evaluation, ?User $evaluator, ?string $actionUrl = null)
    {
        $this->document = $document;
        $this->evaluation = $evaluation;
        $this->evaluator = $evaluator;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject("[e-QMS] Hasil Evaluasi SOP Diserahkan: {$this->document->title}")
                    ->view('emails.evaluation_submitted');
    }
}
