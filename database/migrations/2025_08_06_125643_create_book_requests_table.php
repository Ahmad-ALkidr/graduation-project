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
        Schema::create('book_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->enum('type', ['book', 'summary', 'image']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
