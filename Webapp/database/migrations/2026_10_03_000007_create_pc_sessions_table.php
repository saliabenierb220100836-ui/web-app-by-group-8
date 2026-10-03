<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * pc_sessions is the attendance log: one row per member sitting at one PC.
     * (Named pc_sessions so it does not collide with Laravel's own `sessions` table.)
     */
    public function up(): void
    {
        Schema::create('pc_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('started_at')->index();
            $table->dateTime('ended_at')->nullable();
            $table->decimal('rate_applied', 8, 2);
            $table->string('promo_name')->nullable();
            $table->unsignedInteger('minutes')->nullable();
            $table->unsignedInteger('billed_minutes')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('payment_status', 10)->default('unpaid'); // unpaid | paid
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_sessions');
    }
};
