<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('master_id')
                ->after('id')
                ->constrained('masters')
                ->cascadeOnDelete();

            $table->foreignId('agent_id')
                ->after('master_id')
                ->constrained('agents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['master_id']);
            $table->dropForeign(['agent_id']);

            $table->dropColumn(['master_id', 'agent_id']);
        });
    }
};
