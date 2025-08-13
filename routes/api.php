<?php

use App\Http\Controllers\Api\AnnouncementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookRequestController;
use App\Http\Controllers\Api\ChatGroupController;
use App\Http\Controllers\Api\ChatStatusController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\GeminiChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\BroadcastAuthController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. المسارات العامة (لا تتطلب تسجيل دخول) ---

// المصادقة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// --- مسارات إعادة تعيين كلمة المرور باستخدام OTP ---
Route::post('/password/email', [PasswordResetController::class, 'sendResetOtp']);
Route::post('/password/code/check', [PasswordResetController::class, 'verifyResetOtp']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);


// --- المسارات العامة للمنشورات ---
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// --- مسار استقبال تحديثات بوت التلجرام ---
// يجب أن يكون المسار سريًا بعض الشيء لمنع الوصول غير المصرح به
Route::post('/telegram/webhook/'.env('TELEGRAM_BOT_TOKEN'), [TelegramWebhookController::class, 'handle']);
Route::get('/announcements', [AnnouncementController::class, 'index']);



// --- 2. المسارات المحمية (تتطلب تسجيل الدخول) ---
Route::middleware('auth:sanctum')->group(function () {

    // المستخدم الحالي
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/device/token', [DeviceController::class, 'updateToken']);

    // --- المسارات المحمية للمنشورات ---
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/posts/{post}', [PostController::class, 'update']); // لتحديث المنشور
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/like', [LikeController::class, 'toggleLike']);

    // --- المسارات المحمية للتعليقات ---
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // --- مسارات المكتبة ---
    Route::get('/library/colleges', [LibraryController::class, 'getColleges']);
    Route::get('/library/colleges/{college}/departments', [LibraryController::class, 'getDepartments']);
    Route::get('/library/departments/{department}/options', [LibraryController::class, 'getCourseOptions']);
    Route::get('/library/subjects', [LibraryController::class, 'getSubjects']);
    Route::get('/library/subjects/{subject}/content', [LibraryController::class, 'getSubjectContent']);
    Route::post('/book-requests', [BookRequestController::class, 'store']);
    Route::delete('/book-requests/{bookRequest}', [BookRequestController::class, 'destroy']);

    // --- مسارات الملف الشخصي (البروفايل) ---
    Route::get('/profile', [ProfileController::class, 'showOwnProfile']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/users/{user}/profile', [ProfileController::class, 'showPublicProfile']);
    Route::get('/users/{user}/posts', [ProfileController::class, 'getUserPosts']);
    Route::get('/users/{user}/library-files', [ProfileController::class, 'getUserLibraryFiles']);
    Route::get('/users/search/{query}', [UserController::class, 'search']);
    Route::get('/users/{user}/full-profile', [ProfileController::class, 'showFullProfile']);


    // --- مسارات مجموعات الدردشة ---
    Route::get('/chat-groups', [ChatGroupController::class, 'index']);
    Route::post('/chat-groups', [ChatGroupController::class, 'store']);
    Route::post('/chat-groups/{group}/join', [ChatGroupController::class, 'join']);
    Route::post('/chat-groups/{group}/leave', [ChatGroupController::class, 'leave']);
    Route::get('/chat-groups/{group}/messages', [MessageController::class, 'index']);
    Route::post('/chat-groups/{group}/messages', [MessageController::class, 'store']);
    Route::post('/chat-groups/{group}/mark-as-read', [ChatStatusController::class, 'markGroupAsRead']);
    Route::get('/chats/unread-count', [ChatStatusController::class, 'getUnreadCount']);

    // --- مسارات الدردشة الخاصة ---
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'getMessages']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);
});
