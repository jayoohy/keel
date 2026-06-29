<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'name',
        'trigger_type',
        'trigger_value',
        'action_basis',
        'action_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'action_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(RuleExecution::class);
    }
}
