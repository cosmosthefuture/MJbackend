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
        Schema::create('mah_jong_room_players', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->integer('seat_position')->nullable(); // assigned when match starts

            $table->boolean('is_ready')->default(false);

            // 🔥 CORE FIELDS
            $table->boolean('has_paid_fee')->default(false);
            $table->boolean('is_active')->default(true); // still belongs to room
            $table->timestamp('last_round_played_at')->nullable();

            $table->timestamps();

            $table->unique(['mah_jong_game_room_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_room_players');
    }
};
