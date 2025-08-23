<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Log::debug('Broadcast Service Initialized'); // تأكيد تحميل الخدمة

        Broadcast::routes([
            'prefix' => 'api',
            'middleware' => ['auth:sanctum']
        ]);

            require base_path('routes/channels.php');
    }
}
