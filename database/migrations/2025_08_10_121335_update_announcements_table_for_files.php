<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // إعادة تسمية العمود ليكون أكثر عمومية
            $table->renameColumn('image_path', 'file_path');

            // إضافة عمود جديد لتخزين نوع الملف
            $table->string('file_type')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->renameColumn('file_path', 'image_path');
            $table->dropColumn('file_type');
        });
    }
};
