<?php
// database/migrations/YYYY_MM_DD_XXXXXX_create_messages_table.php

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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // المجموعة التي تنتمي إليها الرسالة
            $table->foreignId('chat_group_id')->constrained('chat_groups')->onDelete('cascade');

            // المستخدم الذي أرسل الرسالة
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->text('content');
            $table->enum('type', ['text', 'image'])->default('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
