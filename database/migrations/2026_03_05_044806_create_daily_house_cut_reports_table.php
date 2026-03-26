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
        Schema::create('daily_house_cut_reports', function (Blueprint $table) {
            $table->id();

            $table->date('report_date')->unique();

            $table->double('spin_wheel_house_cut')->default(0);
            $table->double('coin_flip_house_cut')->default(0);

            $table->double('total_house_cut')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_house_cut_reports');
    }
};
