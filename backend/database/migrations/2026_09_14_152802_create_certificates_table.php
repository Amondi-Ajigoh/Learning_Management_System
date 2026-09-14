<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();
            $table->string('certificate_url')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index(['user_id', 'issued_at']);
            $table->index(['course_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
