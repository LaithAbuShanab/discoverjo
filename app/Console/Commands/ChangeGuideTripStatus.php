<?php

namespace App\Console\Commands;

use App\Models\GuideTrip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChangeGuideTripStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-guide-trip-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of guide trips when they expire.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // نحسب عدد الرحلات التي ستتغير حالتها
        $count = DB::table('guide_trips')
            ->where('status', 1)
            ->where('end_datetime', '<', $now)
            ->count();

        // ننفذ التحديث بدون استخدام prepared statement لتجنب الخطأ
        DB::unprepared("UPDATE guide_trips SET status = 0, updated_at = NOW() WHERE status = 1 AND end_datetime < NOW()");

        // نسجل الحدث في السجل
        Log::info("Guide trip statuses updated: {$count}");

        // نعرض النتيجة في الـ CLI
        $this->info("Updated {$count} guide trips.");
    }
}
