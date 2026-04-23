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
        Schema::create('mah_jong_round_players', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_game_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('seat_position');
            $table->boolean('is_first_player')->default(false);
            $table->boolean('is_winner')->default(false);

            $table->boolean('is_active')->default(true); // still in round
            $table->boolean('is_auto')->default(false); // auto-play

            $table->timestamp('last_action_at')->nullable();

            $table->timestamps();

            $table->unique(['mah_jong_game_round_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_round_players');
    }
};
