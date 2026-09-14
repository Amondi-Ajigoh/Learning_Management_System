<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['document', 'video', 'audio', 'link', 'presentation', 'other']);
            $table->string('url')->nullable();
            $table->string('storage_disk')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_downloadable')->default(false);
            $table->timestamps();

            $table->index(['lesson_id', 'position']);
            $table->index(['type', 'storage_disk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
    }
};
