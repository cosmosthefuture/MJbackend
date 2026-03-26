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
        Schema::create('daily_agent_deposit_commission_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('agents')
                ->cascadeOnDelete();

            $table->date('report_date');

            $table->double('deposit_commission_amount')->default(0);

            $table->timestamps();

            $table->unique(['agent_id', 'report_date'], 'agent_daily_commission_unique');
            $table->index(['agent_id', 'report_date'], 'agent_daily_commission_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_agent_deposit_commission_reports');
    }
};
