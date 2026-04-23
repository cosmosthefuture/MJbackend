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
        Schema::create('mah_jong_game_rounds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_match_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('round_no');

            $table->foreignId('winner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', ['waiting', 'playing', 'finished'])
                ->default('waiting');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_game_rounds');
    }
};
