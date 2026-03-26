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
        Schema::create('agent_incentives', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time');
            $table->string('month')->index();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('user_id');
            $table->string('agent_code');

            $table->double('deposit_amount');
            $table->integer('incentive_percentage');
            $table->double('incentive_amount');

            $table->index('agent_id');
            $table->index('user_id');
            $table->index('agent_code');

            $table->foreign('agent_id')->references('id')->on('agents');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_incentives');
    }
};
