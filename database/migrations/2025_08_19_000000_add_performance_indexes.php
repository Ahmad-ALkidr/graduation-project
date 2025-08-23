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
        Schema::table('users', function (Blueprint $table) {
            // إضافة indexes للبحث
            $table->index(['first_name', 'last_name'], 'idx_users_search');
            $table->index(['email'], 'idx_users_email');
            $table->index(['role'], 'idx_users_role');
        });

        Schema::table('posts', function (Blueprint $table) {
            // إضافة indexes للمنشورات
            $table->index(['created_at'], 'idx_posts_created_at');
            $table->index(['user_id', 'created_at'], 'idx_posts_user_created');
        });

        Schema::table('comments', function (Blueprint $table) {
            // إضافة indexes للتعليقات
            $table->index(['post_id', 'created_at'], 'idx_comments_post_created');
            $table->index(['user_id'], 'idx_comments_user');
        });

        Schema::table('likes', function (Blueprint $table) {
            // إضافة indexes للإعجابات
            $table->index(['post_id', 'user_id'], 'idx_likes_post_user');
        });

        Schema::table('book_requests', function (Blueprint $table) {
            // إضافة indexes لطلبات الكتب
            $table->index(['course_id', 'status'], 'idx_book_requests_course_status');
            $table->index(['user_id'], 'idx_book_requests_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_search');
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_created_at');
            $table->dropIndex('idx_posts_user_created');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_post_created');
            $table->dropIndex('idx_comments_user');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('idx_likes_post_user');
        });

        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropIndex('idx_chat_groups_dept_year');
        });

        Schema::table('book_requests', function (Blueprint $table) {
            $table->dropIndex('idx_book_requests_course_status');
            $table->dropIndex('idx_book_requests_user');
        });
    }
};
