<?php
// database/migrations/YYYY_MM_DD_XXXXXX_create_chat_group_user_table.php

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
        // هذا جدول وسيط لعلاقة متعدد إلى متعدد بين المستخدمين والمجموعات
        Schema::create('chat_group_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_group_id')->constrained('chat_groups')->onDelete('cascade');

            $table->primary(['user_id', 'chat_group_id']);

            // لإضافة أعمدة created_at و updated_at التي يتوقعها Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_group_user');
    }
};
