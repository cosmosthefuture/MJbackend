<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_game_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('game_type'); 

            $table->double('bet_amount')->default(0);
            $table->double('win_amount')->default(0);

            $table->string('status');

            $table->string('room_name');

            $table->integer('round_number');

            $table->unsignedBigInteger('game_round_id');

            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['game_type']);
            $table->index(['game_round_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_game_histories');
    }
};
