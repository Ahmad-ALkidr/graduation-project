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
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // معرّف الأكاديمي الذي أنشأ المجموعة
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');

            // القسم الذي تتبع له المجموعة
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');

            // السنة الدراسية الموجهة لها المجموعة
            $table->integer('year');

            // المقرر الدراسي (اختياري، إذا كانت مجموعة خاصة بمادة)
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_groups');
    }
};
