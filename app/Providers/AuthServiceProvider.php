<?php

namespace App\Providers;

use App\Enums\RoleEnum; // <-- إضافة مهمة
use App\Models\User;
use App\Models\Subject;
use App\Models\ChatGroup;
use App\Models\Conversation;
use App\Policies\ChatGroupPolicy;
use App\Policies\ConversationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
        ChatGroup::class => ChatGroupPolicy::class,
        Conversation::class => ConversationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // --- التصحيح الرئيسي هنا ---
        Gate::define('is-admin', fn(User $user) => $user->role === RoleEnum::ADMIN);
        Gate::define('is-academic', fn(User $user) => $user->role === RoleEnum::ACADEMIC);
        Gate::define('is-student', fn(User $user) => $user->role === RoleEnum::STUDENT);

        Gate::define('manages-subject', function (User $user, Subject $subject) {
            if ($user->role !== RoleEnum::ACADEMIC) {
                return false;
            }
            return $user->id == $subject->academic_id;
        });
    }
}
