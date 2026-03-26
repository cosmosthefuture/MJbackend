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
        Schema::create('coin_flip_bets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coin_flip_round_id')
                ->constrained('coin_flip_rounds')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('side', ['HEAD', 'TAIL']);

            $table->double('bet_amount');

            $table->timestamps();

            $table->unique(
                ['coin_flip_round_id', 'user_id', 'side'],
                'coin_flip_unique_user_side'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_flip_bets');
    }
};
