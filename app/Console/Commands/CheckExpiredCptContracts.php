<?php

namespace App\Console\Commands;

use App\Mail\CptContractExpiredMail;
use App\Models\CptContract;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiredCptContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpt:check-expired {--contract_id= : ID Kontrak spesifik yang ingin dinotifikasi} {--force : Paksa kirim meskipun sudah dikirim hari ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa kontrak CPT yang telah expired dan kirim notifikasi email secara otomatis ke PIC Staf CPT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa data kontrak CPT expired...');
        $result = self::processExpiredNotifications(
            $this->option('contract_id'),
            (bool) $this->option('force'),
            function($msg) {
                $this->line($msg);
            }
        );

        $this->info("Selesai. Total {$result['sent_count']} notifikasi email diproses.");
        return 0;
    }

    /**
     * Reusable core logic for automatic expired checks & notifications
     */
    public static function processExpiredNotifications($specificContractId = null, bool $force = false, ?callable $logger = null): array
    {
        // 1. Ambil PIC Staf CPT yang ditunjuk
        $picUsers = User::where('can_manage_cpt_contracts', true)->whereNotNull('email')->get();

        if ($picUsers->isEmpty()) {
            // Fallback ke user CPT atau Admin
            $picUsers = User::where('role', 'like', '%CPT%')->whereNotNull('email')->get();
            if ($picUsers->isEmpty()) {
                $picUsers = User::where('role', 'admin')->whereNotNull('email')->get();
            }
        }

        if ($picUsers->isEmpty()) {
            if ($logger) $logger('Tidak ada user penerima (PIC CPT / Admin) yang memiliki email.');
            return ['status' => 'no_recipients', 'sent_count' => 0];
        }

        $query = CptContract::query();

        if ($specificContractId) {
            $query->where('id', $specificContractId);
        } else {
            $today = date('Y-m-d');
            $query->where(function($q) use ($today) {
                $q->where('status', 'expired')
                  ->orWhere(function($sub) use ($today) {
                      $sub->whereNotNull('end_date')
                          ->where('end_date', '<=', $today)
                          ->where('status', '!=', 'completed');
                  });
            });
        }

        $expiredContracts = $query->get();
        $sentCount = 0;
        $todayStr = date('Y-m-d');

        foreach ($expiredContracts as $contract) {
            // Auto update status to expired if end_date has passed and status is not completed
            $shouldUpdateStatus = ($contract->status !== 'completed' && $contract->end_date && $contract->end_date->format('Y-m-d') <= $todayStr);
            if ($shouldUpdateStatus && $contract->status !== 'expired') {
                $contract->status = 'expired';
            }

            // Cek apakah sudah pernah dinotifikasi hari ini jika tidak forced dan bukan single contract
            if (!$force && !$specificContractId && $contract->last_notified_at) {
                if ($contract->last_notified_at->toDateString() === $todayStr) {
                    if ($logger) $logger("Kontrak #{$contract->project_number} sudah dinotifikasi hari ini. Melewati...");
                    if ($shouldUpdateStatus) $contract->save();
                    continue;
                }
            }

            $editUrl = route('library.index', [
                'category'         => 'divisi',
                'div'              => 'KOMERSIL',
                'bu'               => 'CPT & MHM',
                'tab'              => 'contracts',
                'edit_contract_id' => $contract->id,
            ]);

            $contractSent = false;
            foreach ($picUsers as $pic) {
                try {
                    Mail::to($pic->email)->queue(new CptContractExpiredMail($contract, $pic, $editUrl));
                    $sentCount++;
                    $contractSent = true;
                    if ($logger) $logger("Notifikasi email diantrikan ke {$pic->email} untuk kontrak #{$contract->project_number} ({$contract->project_name})");
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email notifikasi kontrak expired ID {$contract->id}: " . $e->getMessage());
                }
            }

            if ($contractSent) {
                $contract->last_notified_at = now();
            }
            $contract->save();
        }

        return ['status' => 'success', 'sent_count' => $sentCount];
    }
}
