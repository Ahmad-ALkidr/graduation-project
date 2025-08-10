<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ichtrojan\Otp\Models\Otp;
use Carbon\Carbon;

class ClearExpiredOtps extends Command
{
    protected $signature = 'otp:clear-expired';
    protected $description = 'Deletes expired and unused OTP codes from the database.';

    public function handle()
    {
        $this->info('Starting deletion of expired OTP codes...');

        $expiredOtps = Otp::where('expires_at', '<', Carbon::now())->get();

        if ($expiredOtps->isEmpty()) {
            $this->info('No expired OTP codes found for deletion.');
            return 0;
        }

        $deletedCount = 0;
        foreach ($expiredOtps as $otp) {
            $otp->delete();
            $deletedCount++;
        }

        $this->info("Successfully deleted {$deletedCount} expired OTP codes.");
        return 0;
    }
}