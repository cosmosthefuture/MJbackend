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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_room_id');
            $table->unsignedBigInteger('user_id');

            $table->text('message');
            $table->enum('message_type', ['text', 'image', 'system'])
                ->default('text');

            $table->timestamps();

            $table->index('game_room_id');
            $table->index('user_id');
            $table->index('created_at');

            $table->index(['game_room_id', 'created_at']);

            $table->foreign('game_room_id')
                ->references('id')
                ->on('game_rooms')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
