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
        Schema::create('coin_flip_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coin_flip_round_id')
                ->constrained('coin_flip_rounds')
                ->cascadeOnDelete();

            $table->enum('result_side', ['HEAD', 'TAIL']);

            // Player with highest total bet
            // $table->foreignId('main_player_user_id')
            //     ->nullable()
            //     ->constrained('users')
            //     ->nullOnDelete();

            $table->double('total_head_bet');
            $table->double('total_tail_bet');

            $table->double('total_pot');
            $table->double('house_cut');

            $table->timestamps();

            $table->unique('coin_flip_round_id', 'coin_flip_result_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_flip_results');
    }
};
