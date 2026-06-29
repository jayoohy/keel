<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_connection_id',
        'user_id',
        'mono_account_id',
        'name',
        'account_number',
        'account_type',
        'currency',
        'balance',
        'balance_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'balance_synced_at' => 'datetime',
        ];
    }

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
