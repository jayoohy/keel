<?php

namespace App\Console\Commands;

use App\Jobs\SyncBankConnection;
use App\Models\BankConnection;
use Illuminate\Console\Command;

class SyncBankConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-bank-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a Mono re-sync for every active bank connection';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        BankConnection::where('status', 'active')->each(
            fn (BankConnection $connection) => SyncBankConnection::dispatch($connection)
        );
    }
}
