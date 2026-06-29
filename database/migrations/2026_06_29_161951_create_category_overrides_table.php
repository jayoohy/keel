<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            // Matched against a transaction's description/narration on import.
            $table->string('match_pattern');
            $table->timestamps();

            $table->unique(['user_id', 'match_pattern']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_overrides');
    }
};
