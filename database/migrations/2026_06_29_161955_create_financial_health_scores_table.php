<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->decimal('savings_rate', 5, 2)->default(0);
            $table->decimal('emergency_fund_coverage', 5, 2)->default(0);
            $table->decimal('spending_stability', 5, 2)->default(0);
            $table->decimal('income_consistency', 5, 2)->default(0);
            $table->timestamp('computed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_health_scores');
    }
};
