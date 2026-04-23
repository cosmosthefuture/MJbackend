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
        Schema::create('mah_jong_round_tiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_round_id')
                ->constrained('mah_jong_game_rounds')
                ->cascadeOnDelete();

            $table->foreignId('mah_jong_tile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('location', [
                'wall',
                'hand',
                'discard',
                'meld'
            ]);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->integer('sequence_order')->nullable();

            $table->timestamps();

            $table->index(['mah_jong_round_id', 'location']);
            $table->index(['mah_jong_round_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_round_tiles');
    }
};
