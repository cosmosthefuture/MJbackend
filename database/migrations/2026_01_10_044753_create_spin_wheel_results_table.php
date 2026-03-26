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
        Schema::create('spin_wheel_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_wheel_round_id')
                ->constrained('spin_wheel_rounds')
                ->cascadeOnDelete();

            $table->foreignId('winner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->double('total_pot');
            $table->double('house_cut');
            $table->double('winner_payout');

            $table->timestamps();

            $table->unique('spin_wheel_round_id', 'spin_wheel_result_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_results');
    }
};
