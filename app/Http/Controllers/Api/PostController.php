<?php

namespace App\Http\Controllers\Api;

use App\Events\PostCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use DB;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * عرض كل المنشورات بشكل عام للجميع
     */
    public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 20), 50);
        $userId = $request->input('id');

        $posts = Post::with('user')
            ->withCount(['likers', 'comments'])
            ->withCount([
                'likers as is_liked_by_user' => function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        // إذا لم يكن هناك userId، لا نضيف أي شرط
                        $query->whereRaw('0=1'); // سيجعل count = 0 دائمًا
                    }
                },
            ])
            ->latest()
            ->paginate($perPage);

        return PostResource::collection($posts);
    }

    /**
     * إنشاء منشور جديد
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validate([
            'content' => 'required_without:image|nullable|string|max:10000',
            'image' => 'required_without:content|nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/posts');
            $imagePath = str_replace('public/', '', $path);
        }

        $post = $request
            ->user()
            ->posts()
            ->create([
                // استخدام '?? null' لضمان عدم حدوث خطأ إذا كان الحقل غير موجود
                'content' => $validated['content'] ?? null,
                'image_path' => $imagePath,
            ]);

        $post->load('user');

        // بث حدث إنشاء المنشور (للإشعارات اللحظية إذا كانت مفعلة)
        PostCreated::dispatch($post);

        // تم إزالة إرسال الإشعار الخاص بالقسم ليتناسب مع منطق الصفحة العامة

        return new PostResource($post);
    }

    /**
     * عرض منشور واحد محدد
     */
    public function show(Post $post)
    {
        // نقوم بتحميل العلاقات اللازمة للتأكد من أن كل البيانات موجودة
        $post->load('user')->loadCount(['likers', 'comments']);

        return new PostResource($post);
    }

    /**
     * تحديث منشور
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        $post->update($request->validated());
        return new PostResource($post->fresh()->load('user'));
    }

    /**
     * حذف منشور
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->noContent();
    }
}
