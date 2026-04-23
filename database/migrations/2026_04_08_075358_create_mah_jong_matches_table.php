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
        Schema::create('mah_jong_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_game_room_id')->constrained()->cascadeOnDelete();

            $table->foreignId('first_player_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');

            $table->integer('total_rounds')->default(0);

            $table->foreignId('current_round_id')->nullable();
            $table->foreignId('current_turn_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_matches');
    }
};
