<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'projected_completion_date',
        'average_monthly_saving',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'projected_completion_date' => 'date',
            'average_monthly_saving' => 'decimal:2',
            'computed_at' => 'datetime',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
