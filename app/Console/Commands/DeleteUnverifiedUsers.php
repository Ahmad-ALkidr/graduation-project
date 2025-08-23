<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DeleteUnverifiedUsers extends Command
{
    protected $signature = 'users:delete-unverified';
    protected $description = 'Deletes unverified user accounts after a specified period.';

    public function handle()
    {
        $this->info('Starting deletion of unverified users...');

        // تحديد الفترة الزمنية التي بعدها يعتبر الحساب غير مؤكد وقابل للحذف
        // مثال: إذا لم يتم التحقق من البريد خلال 24 ساعة (1440 دقيقة) من إرسال أول OTP (أو إنشاء الحساب)
        $thresholdMinutes = 1440; // 24 ساعة

        // استعلام للحسابات غير المؤكدة
        $unverifiedUsers = User::whereNull('email_verified_at')
                                ->where(function ($query) use ($thresholdMinutes) {
                                    $query->where('otp_sent_at', '<', Carbon::now()->subMinutes($thresholdMinutes));
                                })
                                ->get();

        if ($unverifiedUsers->isEmpty()) {
            $this->info('No unverified users found for deletion.');
            return 0;
        }

        $deletedCount = 0;
        foreach ($unverifiedUsers as $user) {
            $user->delete();
            $deletedCount++;
        }

        $this->info("Successfully deleted {$deletedCount} unverified users.");
        return 0;
    }
}