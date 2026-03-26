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
        Schema::create('mah_jong_game_rule_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mah_jong_game_rule_id')
                ->constrained('mah_jong_game_rules')
                ->cascadeOnDelete();

            $table->enum('fee_type', [
                'room',
                'registration',
                'winning_commission'
            ]);

            $table->double('amount');

            $table->enum('payer_type', [
                'winner',
                'each_player'
            ]);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['mah_jong_game_rule_id', 'fee_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_game_rule_fees');
    }
};
