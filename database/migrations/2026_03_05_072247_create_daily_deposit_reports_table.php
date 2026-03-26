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
        Schema::create('daily_deposit_reports', function (Blueprint $table) {
            $table->id();

            $table->date('report_date')->unique();

            $table->double('request_deposit')->default(0);
            $table->double('manual_deposit')->default(0);

            $table->double('total_deposit')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_deposit_reports');
    }
};
