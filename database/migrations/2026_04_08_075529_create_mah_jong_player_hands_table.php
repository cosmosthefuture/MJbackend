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
        Schema::create('mah_jong_player_hands', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_match_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('mah_jong_game_round_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->json('tiles'); // [tile_ids]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_player_hands');
    }
};
