<?php

namespace App\Mail;

use App\Models\NewSopRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSopRequestSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public NewSopRequest $sopRequest;
    public ?User $submitter;
    public ?string $actionUrl;

    public function __construct(NewSopRequest $sopRequest, ?User $submitter, ?string $actionUrl = null)
    {
        $this->sopRequest = $sopRequest;
        $this->submitter = $submitter;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject("[e-QMS] Usulan SOP Baru Masuk: {$this->sopRequest->title}")
                    ->view('emails.new_sop_request_submitted');
    }
}
