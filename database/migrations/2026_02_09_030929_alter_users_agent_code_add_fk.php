<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('agent_code')
            ->update(['agent_code' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('agent_code')
                ->nullable(false)
                ->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('agent_code')
                ->references('agent_code')
                ->on('agents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['agent_code']);

            $table->string('agent_code')
                ->nullable()
                ->change();
        });
    }
};
