<?php

namespace App\Services\Mono;

use App\Models\Account;
use App\Models\BankConnection;
use App\Models\SyncLog;
use App\Models\Transaction;
use App\Notifications\SyncErrorAlert;
use App\Services\Rules\RuleEvaluator;
use App\Services\Transactions\TransactionCategorizer;
use Illuminate\Http\Client\RequestException;
use Throwable;

class MonoSyncService
{
    public function __construct(
        private MonoClient $mono,
        private TransactionCategorizer $categorizer,
        private RuleEvaluator $ruleEvaluator,
    ) {}

    public function sync(BankConnection $connection): SyncLog
    {
        $startedAt = now();

        try {
            $account = $this->upsertAccount($connection);
            $imported = $this->importTransactions($account);

            if ($connection->status !== 'active') {
                $connection->update(['status' => 'active']);
            }

            return SyncLog::create([
                'user_id' => $connection->user_id,
                'bank_connection_id' => $connection->id,
                'status' => 'success',
                'transactions_synced' => $imported,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);
        } catch (RequestException $e) {
            if ($e->response->status() === 401) {
                $connection->update(['status' => 'reauth_required']);
            }

            return $this->failedLog($connection, $startedAt, $e->getMessage());
        } catch (Throwable $e) {
            return $this->failedLog($connection, $startedAt, $e->getMessage());
        }
    }

    private function failedLog(BankConnection $connection, \DateTimeInterface $startedAt, string $message): SyncLog
    {
        $log = SyncLog::create([
            'user_id' => $connection->user_id,
            'bank_connection_id' => $connection->id,
            'status' => 'failed',
            'message' => $message,
            'started_at' => $startedAt,
            'finished_at' => now(),
        ]);

        $connection->user->notify(new SyncErrorAlert($connection, $message));

        return $log;
    }

    private function upsertAccount(BankConnection $connection): Account
    {
        $payload = $this->mono->getAccount($connection->mono_account_id);
        $data = $payload['data'] ?? $payload;
        $accountInfo = $data['account'] ?? $data;
        $institution = $data['institution'] ?? null;

        if ($institution) {
            $connection->fill([
                'institution_name' => $institution['name'] ?? $connection->institution_name,
                'institution_logo' => $institution['logo'] ?? $connection->institution_logo,
            ])->save();
        }

        return Account::updateOrCreate(
            ['mono_account_id' => $connection->mono_account_id],
            [
                'bank_connection_id' => $connection->id,
                'user_id' => $connection->user_id,
                'name' => $accountInfo['name'] ?? 'Account',
                'account_number' => $accountInfo['accountNumber'] ?? null,
                'account_type' => $accountInfo['type'] ?? null,
                'currency' => $accountInfo['currency'] ?? 'NGN',
                'balance' => isset($accountInfo['balance']) ? $accountInfo['balance'] / 100 : 0,
                'balance_synced_at' => now(),
            ]
        );
    }

    private function importTransactions(Account $account): int
    {
        $page = 1;
        $imported = 0;

        do {
            $response = $this->mono->getTransactions($account->mono_account_id, $page);
            $transactions = $response['data'] ?? [];

            foreach ($transactions as $payload) {
                $transaction = Transaction::firstOrCreate(
                    ['mono_transaction_id' => $payload['id']],
                    [
                        'account_id' => $account->id,
                        'user_id' => $account->user_id,
                        'type' => $this->mapType($payload),
                        'amount' => abs($payload['amount']) / 100,
                        'currency' => $payload['currency'] ?? 'NGN',
                        'description' => $payload['narration'] ?? null,
                        'narration' => $payload['narration'] ?? null,
                        'balance_after' => isset($payload['balance']) ? $payload['balance'] / 100 : null,
                        'transacted_at' => $payload['date'] ?? now(),
                    ]
                );

                if ($transaction->wasRecentlyCreated) {
                    $imported++;

                    if ($category = $this->categorizer->categorize($transaction)) {
                        $transaction->update(['category_id' => $category->id]);
                    }

                    $this->ruleEvaluator->evaluate($transaction);
                }
            }

            $totalPages = (int) ($response['meta']['total_pages'] ?? 1);
            $page++;
        } while ($page <= $totalPages);

        return $imported;
    }

    private function mapType(array $payload): string
    {
        $type = strtolower($payload['type'] ?? 'debit');

        return in_array($type, ['debit', 'credit', 'transfer', 'fee', 'salary', 'refund'], true) ? $type : 'debit';
    }
}
