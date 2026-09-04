<?php

namespace App\Mail;

use App\Models\CptContract;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CptContractExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public CptContract $contract;
    public User $recipient;
    public string $editUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(CptContract $contract, User $recipient, ?string $editUrl = null)
    {
        $this->contract = $contract;
        $this->recipient = $recipient;
        $this->editUrl = $editUrl ?? route('library.index', ['bu' => 'CPT & MHM', 'active_tab' => 'contracts', 'edit_contract_id' => $contract->id]);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[PENTING] Pemberitahuan Kontrak Telah Expired - BU CPT (' . ($this->contract->project_name ?? $this->contract->project_number) . ')')
                    ->view('emails.cpt_contract_expired');
    }
}
