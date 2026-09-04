<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function normalize(?string $number): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $number);
        if ($number === '') return null;
        if (str_starts_with($number, '0')) $number = '62' . substr($number, 1);
        if (!str_starts_with($number, '62') || strlen($number) < 10 || strlen($number) > 15) return null;
        return $number;
    }

    public function send(?string $number, string $message): bool
    {
        $recipient = $this->normalize($number);
        $token = config('services.fonnte.token');
        if (!$recipient || !$token) {
            Log::warning('WhatsApp notification skipped: nomor atau konfigurasi tidak valid.');
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout((int) config('services.fonnte.timeout', 10))
                ->post(config('services.fonnte.endpoint'), ['target' => $recipient, 'message' => $message]);
            if (!$response->successful()) {
                Log::error('WhatsApp notification failed', ['status' => $response->status()]);
            }
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('WhatsApp notification exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}
