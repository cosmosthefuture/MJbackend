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
        Schema::create('game_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->double('max_bet_amount');
            $table->double('min_bet_amount');
            $table->integer('time_per_round');
            $table->unsignedInteger('user_limit')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->unique(['rule_name', 'game_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rules');
    }
};
