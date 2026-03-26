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
        Schema::create('coin_flip_rounds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_room_id')
                ->constrained('game_rooms')
                ->cascadeOnDelete();

            $table->unsignedInteger('round_number');

            $table->enum('status', ['betting', 'flipping', 'finished'])
                ->default('betting');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('betting_closed_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->double('total_head_bet')->default(0);
            $table->double('total_tail_bet')->default(0);

            $table->double('head_winning_chance_percentage')->default(0);
            $table->double('tail_winning_chance_percentage')->default(0);

            $table->timestamps();

            $table->unique(['game_room_id', 'round_number'], 'coin_flip_round_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_flip_rounds');
    }
};
