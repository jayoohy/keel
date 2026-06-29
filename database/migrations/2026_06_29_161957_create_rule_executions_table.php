<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allocation_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('executed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_executions');
    }
};
