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
        Schema::create('spin_wheel_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')
                ->constrained('game_rooms')
                ->cascadeOnDelete();

            $table->unsignedInteger('round_number');

            $table->enum('status', ['betting', 'spinning', 'finished'])
                ->default('betting');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('betting_closed_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->unique(['game_room_id', 'round_number'], 'spin_wheel_round_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_rounds');
    }
};
