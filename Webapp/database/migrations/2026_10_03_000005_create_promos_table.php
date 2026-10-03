<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('badge_label', 40)->nullable();
            $table->string('discount_type', 20)->default('percent'); // none | percent | fixed_rate
            $table->decimal('discount_value', 8, 2)->default(0);
            $table->string('applies_to', 10)->default('all');         // all | standard | vip
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('requires_student')->default(false);
            $table->string('image_path')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
