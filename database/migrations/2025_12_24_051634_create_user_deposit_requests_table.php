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
        Schema::create('user_deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->double('amount');
            $table->string('last_six_digits_of_payment_slip',6);
            $table->string('payment_slip_image_url');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('reason_for_rejection')->nullable();
            $table->foreignId('action_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_deposit_requests');
    }
};
