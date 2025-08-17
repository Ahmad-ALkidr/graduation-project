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
        Schema::table('private_messages', function (Blueprint $table) {
            // إضافة indexes لتحسين الأداء
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_id', 'is_read']);
            $table->index(['conversation_id', 'sender_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('private_messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'created_at']);
            $table->dropIndex(['sender_id', 'is_read']);
            $table->dropIndex(['conversation_id', 'sender_id', 'is_read']);
        });
    }
};
