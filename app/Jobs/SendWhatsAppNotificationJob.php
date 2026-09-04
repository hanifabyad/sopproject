<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 180];
    public function __construct(public ?string $number, public string $message) {}
    public function handle(WhatsAppService $service): void { $service->send($this->number, $this->message); }
    public function failed(\Throwable $e): void { Log::error('WhatsApp job permanently failed', ['message' => $e->getMessage()]); }
}
