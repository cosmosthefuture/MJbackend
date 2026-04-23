<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mah_jong_match_players', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->integer('seat_position'); // ✅ FIXED per match

            $table->boolean('is_active')->default(true); // match-level

            $table->timestamps();

            $table->unique(['mah_jong_match_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_match_players');
    }
};
