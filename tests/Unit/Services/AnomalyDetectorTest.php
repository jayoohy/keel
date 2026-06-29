<?php

use App\Models\Transaction;
use App\Models\User;
use App\Services\AI\AnomalyDetector;

test('it flags a transaction far above the recent average', function () {
    $user = User::factory()->create();

    foreach (range(1, 8) as $i) {
        Transaction::factory()->for($user)->create([
            'amount' => 1000,
            'transacted_at' => now()->subDays($i),
        ]);
    }

    $anomaly = Transaction::factory()->for($user)->create([
        'amount' => 100000,
        'transacted_at' => now(),
    ]);

    $flagged = app(AnomalyDetector::class)->detect($user);

    expect($flagged->pluck('id'))->toContain($anomaly->id);
    expect($flagged)->toHaveCount(1);
});

test('it does not flag anything when there is too little history', function () {
    $user = User::factory()->create();
    Transaction::factory()->for($user)->create(['amount' => 100000]);

    $flagged = app(AnomalyDetector::class)->detect($user);

    expect($flagged)->toHaveCount(0);
});

test('it does not flag anything when spending is consistent', function () {
    $user = User::factory()->create();

    foreach (range(1, 8) as $i) {
        Transaction::factory()->for($user)->create([
            'amount' => 1000,
            'transacted_at' => now()->subDays($i),
        ]);
    }

    $flagged = app(AnomalyDetector::class)->detect($user);

    expect($flagged)->toHaveCount(0);
});
