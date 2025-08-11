<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FullProfileResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\PrivateProfileResource;
use App\Http\Resources\PublicProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * عرض البروفايل الخاص بالمستخدم المسجل حالياً
     */
    public function showOwnProfile(Request $request)
    {
        return new PrivateProfileResource($request->user());
    }

    /**
     * عرض البروفايل العام لأي مستخدم
     */
    public function showPublicProfile(User $user)
    {
        return new PublicProfileResource($user);
    }
        public function showFullProfile(User $user)
    {
        // ببساطة، قم بإرجاع الـ Resource الجديد للمستخدم المطلوب
        return new FullProfileResource($user);
    }

    /**
     * تحديث البيانات العامة للبروفايل (الاسم، الصورة، إلخ)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'profile_picture' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // تحديث الاسم الأول إذا كان موجوداً وغير فارغ
        if ($request->filled('first_name')) {
            $user->first_name = $request->input('first_name');
        }

        // تحديث اسم العائلة إذا كان موجوداً وغير فارغ
        if ($request->filled('last_name')) {
            $user->last_name = $request->input('last_name');
        }

        // تحديث الصورة الشخصية إذا كانت موجودة
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::delete('public/' . $user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('public/profile_pictures');
            $user->profile_picture = str_replace('public/', '', $path);
        }

        // حفظ كل التغييرات في قاعدة البيانات
        $user->save();

        return new PrivateProfileResource($user);
    }


    /**
     * دالة لتغيير كلمة المرور
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // التحقق مما إذا كانت كلمة المرور الحالية صحيحة
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة.'], 422);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
    }

    /**
     * دالة لجلب كل المناشير الخاصة بمستخدم معين
     */
    public function getUserPosts(User $user)
    {
        // نفترض أن لديك علاقة 'posts' في موديل User
        $posts = $user->posts()->with('user')->latest()->get();

        return PostResource::collection($posts);
    }

    /**
     * دالة لجلب كل الملفات المعتمدة التي رفعها مستخدم معين في المكتبة
     */
    public function getUserLibraryFiles(User $user)
    {
        // جلب طلبات الكتب المعتمدة فقط لهذا المستخدم
        $files = $user->bookRequests()
            ->where('status', 'approved')
            ->with('course.subject:id,name') // جلب بيانات مفيدة عن المادة
            ->latest()
            ->paginate(15);

        return response()->json($files);
    }
}
