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
        Schema::create('agent_wallet_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->date('date');

            $table->double('opening_balance')->default(0);
            $table->double('total_in')->default(0);
            $table->double('total_out')->default(0);
            $table->double('closing_balance')->default(0);

            $table->timestamps();

            $table->unique(['agent_id', 'date']);
            $table->index(['agent_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_wallet_daily_summaries');
    }
};
