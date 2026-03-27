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
        Schema::create('master_wallet_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')
                ->constrained('masters')
                ->cascadeOnDelete();
            $table->dateTime('date_time');
            $table->enum('type', ['in', 'out']);
            $table->double('amount');
            $table->double('balance');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_wallet_records');
    }
};
