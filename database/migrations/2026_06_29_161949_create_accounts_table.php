<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_connection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mono_account_id')->unique();
            $table->string('name');
            $table->string('account_number', 20)->nullable();
            $table->string('account_type')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamp('balance_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
