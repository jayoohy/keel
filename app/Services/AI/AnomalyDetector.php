<?php

namespace App\Services\AI;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Flags transactions far above the user's recent historical average. Pure
 * statistics — no AI call needed for the detection itself (PRD §4.11 #40).
 */
class AnomalyDetector
{
    private const LOOKBACK_DAYS = 30;

    private const STD_DEV_THRESHOLD = 2;

    private const MIN_SAMPLE_SIZE = 5;

    /**
     * @return Collection<int, Transaction>
     */
    public function detect(User $user): Collection
    {
        $transactions = $user->transactions()
            ->where('transacted_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->get();

        if ($transactions->count() < self::MIN_SAMPLE_SIZE) {
            return collect();
        }

        $amounts = $transactions->map(fn (Transaction $t) => (float) $t->amount);
        $mean = $amounts->avg();
        $stdDev = sqrt($amounts->map(fn ($amount) => ($amount - $mean) ** 2)->avg());

        if ($stdDev <= 0) {
            return collect();
        }

        $threshold = $mean + (self::STD_DEV_THRESHOLD * $stdDev);

        return $transactions->filter(fn (Transaction $t) => (float) $t->amount > $threshold)->values();
    }
}
