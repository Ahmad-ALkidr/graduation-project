<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('Please create at least two users to run this seeder.');
            return;
        }

        // تأكد من وجود المجلد، وإذا لم يكن موجودًا، قم بإنشائه
        if (!Storage::exists('public/posts')) {
            Storage::makeDirectory('public/posts');
        }

        for ($i = 0; $i < 10; $i++) {
            $author = $users->random();
            $imagePath = null;

            // إضافة صورة حقيقية لبعض المنشورات
            if (rand(0, 1)) {
                try {
                    // 1. تنزيل صورة عشوائية من الإنترنت
                    $response = Http::get('https://i.pravatar.cc/150?img=7');

                    if ($response->successful()) {
                        // 2. إنشاء اسم فريد للملف
                        $imageName = uniqid() . '.jpg';
                        $imagePathForStorage = 'public/posts/' . $imageName;

                        // 3. حفظ الصورة في مجلد storage
                        Storage::put($imagePathForStorage, $response->body());

                        // 4. تجهيز المسار للحفظ في قاعدة البيانات
                        $imagePath = 'posts/' . $imageName;
                    }
                } catch (\Exception $e) {
                    $this->command->error('Failed to download image: ' . $e->getMessage());
                }
            }

            $post = Post::create([
                'content' => "هذا منشور تجريبي رقم " . ($i + 1) . " من إنشاء " . $author->first_name,
                'user_id' => $author->id,
                'image_path' => $imagePath, // حفظ المسار الصحيح
            ]);

            // إضافة تعليقات وإعجابات عشوائية...
            $commenters = $users->random(rand(0, 5));
            foreach ($commenters as $commenter) {
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                    'content' => 'هذا تعليق تجريبي.',
                ]);
            }

            $likers = $users->where('id', '!=', $author->id)->random(rand(0, $users->count() - 1));
            $post->likers()->attach($likers->pluck('id'));

            // تحديث العدادات
            $post->update([
                'likes_count' => $post->likers()->count(),
                'comments_count' => $post->comments()->count(),
            ]);
        }
    }
}
