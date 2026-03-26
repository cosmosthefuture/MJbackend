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
        Schema::create('agent_incentive_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');

            $table->string('month');

            $table->double('total_deposit_amount')->default(0);
            $table->double('total_incentive_amount')->default(0);

            $table->timestamps();

            $table->unique(['agent_id', 'month']);
            $table->index(['agent_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_incentive_monthly_summaries');
    }
};
