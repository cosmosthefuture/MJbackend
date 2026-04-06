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
        Schema::create('user_deposit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->double('amount');
            $table->foreignId('action_by_agent')
                ->nullable()
                ->constrained('agents')
                ->nullOnDelete();
            $table->foreignId('action_by_master')
                ->nullable()
                ->constrained('masters')
                ->nullOnDelete();
            $table->dateTime('date_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_deposit_records');
    }
};
