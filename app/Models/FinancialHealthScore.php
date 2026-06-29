<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialHealthScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score',
        'savings_rate',
        'emergency_fund_coverage',
        'spending_stability',
        'income_consistency',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'savings_rate' => 'decimal:2',
            'emergency_fund_coverage' => 'decimal:2',
            'spending_stability' => 'decimal:2',
            'income_consistency' => 'decimal:2',
            'computed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
