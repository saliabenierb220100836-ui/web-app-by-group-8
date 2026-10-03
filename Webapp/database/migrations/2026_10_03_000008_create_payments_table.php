<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('pc_session_id')->nullable()->constrained('pc_sessions')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 20);               // account_fee | session
            $table->string('method_name');               // snapshot, survives method deletion
            $table->decimal('amount', 10, 2);
            $table->string('reference_no', 60)->nullable();
            $table->string('proof_path')->nullable();    // private disk
            $table->string('status', 20)->default('pending'); // pending | confirmed | rejected
            $table->string('note')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
