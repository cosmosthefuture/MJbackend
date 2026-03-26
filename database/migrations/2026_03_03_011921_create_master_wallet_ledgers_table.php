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
        Schema::create('master_wallet_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('masters')->onDelete('cascade');

            $table->date('date');
            $table->double('amount');
            $table->enum('type', ['in', 'out']);
            $table->double('balance');

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->timestamps();

            $table->index(['master_id', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_wallet_ledgers');
    }
};
