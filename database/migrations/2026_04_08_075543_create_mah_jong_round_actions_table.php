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
        Schema::create('mah_jong_round_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_match_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('mah_jong_game_round_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('action_type', [
                'draw',
                'discard',
                'pong',
                'chow',
                'kong',
                'win',
                'pass'
            ]);

            $table->json('payload')->nullable();

            $table->integer('turn_no');

            $table->timestamps();

            // 🔥 important
            $table->index(['mah_jong_game_round_id', 'turn_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_round_actions');
    }
};
