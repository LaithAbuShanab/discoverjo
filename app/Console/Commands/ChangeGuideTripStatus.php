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
        $now = now()->toDateTimeString(); // Ensure the format matches your DB

//        $updatedGuideTrips = GuideTrip::where('status', '1')
//            ->where('end_datetime', '<', $now)
//            ->update(['status' => '0']);

        $updatedGuideTrips =DB::unprepared("UPDATE guide_trips SET status = 0, updated_at = NOW() WHERE status = 1 AND end_datetime < NOW()");

        // Log the changes
        Log::info("Guide trip statuses updated: {$updatedGuideTrips}");

        // Output for CLI
        $this->info("Updated {$updatedGuideTrips} guide trips.");
    }
}
