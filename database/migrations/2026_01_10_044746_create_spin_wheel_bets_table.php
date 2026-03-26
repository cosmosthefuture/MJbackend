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
        Schema::create('spin_wheel_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_wheel_round_id')
                ->constrained('spin_wheel_rounds')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->double('bet_amount');
            $table->integer('single_bet_winning_percentage');
            $table->integer('total_winning_chance_percentage');

            $table->timestamps();

            $table->index(['spin_wheel_round_id', 'user_id'], 'spin_wheel_bet_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_bets');
    }
};
