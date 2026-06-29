<?php

namespace App\Jobs;

use App\Models\BankConnection;
use App\Services\Mono\MonoSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBankConnection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BankConnection $bankConnection) {}

    public function handle(MonoSyncService $syncService): void
    {
        $syncService->sync($this->bankConnection);
    }
}
