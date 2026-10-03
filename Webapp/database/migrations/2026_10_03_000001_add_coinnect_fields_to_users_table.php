<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('member')->index();
            $table->string('phone', 30)->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('student_id_no', 50)->nullable();
            $table->boolean('is_student')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'phone', 'avatar_path', 'student_id_no', 'is_student', 'is_active', 'last_login_at']);
        });
    }
};
