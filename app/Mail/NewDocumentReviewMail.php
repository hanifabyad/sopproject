<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class NewDocumentReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $magicLoginUrl; 

    /**
     * Menerima 3 data lengkap dari Controller
     */
    public function __construct(Document $document, User $user, $magicLoginUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        // Jika null, fallback ke login biasa
        $this->magicLoginUrl = $magicLoginUrl ?? route('login'); 
    }

    /**
     * Merakit email menggunakan build() agar semua variabel lolos sempurna
     */
    public function build()
    {
        return $this->subject('🔔 Undangan Review Dokumen Baru - e-QMS')
                    ->view('emails.new_review_request'); 
    }
}
