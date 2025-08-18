<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueues extends Command
{
    protected $signature = 'queue:monitor-status';
    protected $description = 'Monitor queue status and performance';

    public function handle()
    {
        $this->info('🔍 Queue Status Monitor');
        $this->line('');

        // إحصائيات Jobs
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $this->info("📊 Jobs Statistics:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending Jobs', $pendingJobs],
                ['Failed Jobs', $failedJobs],
            ]
        );

        // تحليل Jobs حسب النوع
        if ($pendingJobs > 0) {
            $this->info("📋 Pending Jobs Analysis:");
            $jobTypes = DB::table('jobs')
                ->selectRaw('payload, COUNT(*) as count')
                ->groupBy('payload')
                ->get();

            $jobAnalysis = [];
            foreach ($jobTypes as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['displayName'] ?? 'Unknown';
                $jobAnalysis[] = [$jobClass, $job->count];
            }

            $this->table(['Job Type', 'Count'], $jobAnalysis);
        }

        // تحليل Failed Jobs
        if ($failedJobs > 0) {
            $this->info("❌ Failed Jobs Analysis:");
            $failedJobTypes = DB::table('failed_jobs')
                ->selectRaw('payload, COUNT(*) as count')
                ->groupBy('payload')
                ->get();

            $failedAnalysis = [];
            foreach ($failedJobTypes as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['displayName'] ?? 'Unknown';
                $failedAnalysis[] = [$jobClass, $job->count];
            }

            $this->table(['Failed Job Type', 'Count'], $failedAnalysis);
        }

        // توصيات
        $this->info("💡 Recommendations:");
        if ($pendingJobs > 100) {
            $this->warn("⚠️  High number of pending jobs. Consider increasing workers.");
        }
        if ($failedJobs > 10) {
            $this->error("🚨 High number of failed jobs. Check logs for errors.");
        }
        if ($pendingJobs === 0 && $failedJobs === 0) {
            $this->info("✅ All queues are healthy!");
        }

        return 0;
    }
}
