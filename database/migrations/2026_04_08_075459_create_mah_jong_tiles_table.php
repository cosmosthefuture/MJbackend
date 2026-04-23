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
        Schema::create('mah_jong_tiles', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['dot', 'bamboo']);
            $table->integer('number'); // 1–9
            $table->integer('copy_no'); // 1–4

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_jong_tiles');
    }
};
