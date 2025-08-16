<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'gender',
        'birth_date', 'university_id', 'college',
        'major', 'year', 'role', 'profile_picture',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
        'profile_picture',
        'otp_sent_at',
        'fcm_token',
        'status'

    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'otp_sent_at' => 'datetime',
        'role' => RoleEnum::class,
    ];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_picture_url'];

    /**
     * Get the full URL for the user's profile picture.
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            // أضفنا بصمة زمنية (timestamp) إلى نهاية الرابط
            // هذا يجبر التطبيق على إعادة تحميل الصورة عند تغييرها
            return asset('storage/' . $this->profile_picture) . '?v=' . $this->updated_at->timestamp;
        }

        return null;
    }
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */


    public function isStudent(): bool
    {
        return $this->role === RoleEnum::STUDENT;
    }

    public function isAcademic(): bool
    {
        return $this->role === RoleEnum::ACADEMIC;
    }

    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::ADMIN;
    }

    // public function hasRole(RoleEnum $role): bool
    // {
    //     return $this->role->value === $role->value;
    // }
    public function hasRole(RoleEnum|string $role): bool
{
    if (is_string($role)) {
        $role = RoleEnum::from($role); 
    }

    return $this->role === $role;
}

    // Relations
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * التعليقات التي كتبها المستخدم
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * المنشورات التي أعجب بها المستخدم
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'likes')->withTimestamps();
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_id');
    }

    public function bookRequests()
    {
        return $this->hasMany(BookRequest::class, 'user_id');
    }

    /**
     * العلاقة مع مجموعات الدردشة التي ينتمي إليها المستخدم
     */
    public function chatGroups()
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_user')
            ->withTimestamps()
            ->withPivot('last_read_at');
    }

     /**
     * العلاقة مع المحادثات الخاصة التي يشارك فيها المستخدم
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->withTimestamps();
    }
}
