<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // in the new migration file
    public function up(): void
    {
        Schema::table('conversation_user', function (Blueprint $table) {
            // This column will be null if the user has never read the conversation
            $table->timestamp('last_read_at')->nullable()->after('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_user', function (Blueprint $table) {
            //
        });
    }
};
