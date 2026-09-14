<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer']);
            $table->decimal('points', 8, 2)->default(1);
            $table->unsignedInteger('position');
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
