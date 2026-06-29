<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mono_account_id',
        'access_token',
        'institution_name',
        'institution_logo',
        'status',
        'connected_at',
        'disconnected_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }
}
