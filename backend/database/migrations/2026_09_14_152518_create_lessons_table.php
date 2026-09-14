<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->enum('type', ['text', 'video', 'mixed', 'resource'])->default('text');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('position');
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
            $table->unique(['module_id', 'position']);
            $table->index(['module_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
