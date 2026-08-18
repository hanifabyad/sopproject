<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Models\User;

class DocumentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

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
        return $this->subject('✅ Dokumen Anda Telah Disetujui (Final) - e-QMS')
                    ->view('emails.document_approved'); 
    }
}
