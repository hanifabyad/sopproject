<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Document;
use App\Models\User;

class DocumentApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $backoff = 60;

    public $document;
    public $user;
    public $magicLoginUrl; 
    public $socializationUrl;

    /**
     * Menerima data lengkap dari Controller
     */
    public function __construct(Document $document, User $user, $magicLoginUrl = null, $socializationUrl = null)
    {
        $this->document = $document;
        $this->user = $user;
        // Jika null, fallback ke login biasa
        $this->magicLoginUrl = $magicLoginUrl ?? route('login'); 
        $this->socializationUrl = $socializationUrl ?? route('user.socializations.index');
    }

    /**
     * Merakit email menggunakan build() agar semua variabel lolos sempurna
     */
    public function build()
    {
        return $this->subject("✅ [e-QMS] Dokumen SOP Sah & Aktif: {$this->document->title}")
                    ->view('emails.document_approved'); 
    }
}
