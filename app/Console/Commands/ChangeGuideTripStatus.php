<?php

namespace App\Console\Commands;

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

        // Count how many trips will be updated
        $count = DB::table('guide_trips')
            ->where('status', 1)
            ->where('end_datetime', '<', $now)
            ->count();

        // Use DB::statement to execute the update without using prepared statements
        DB::statement("
            UPDATE guide_trips
            SET status = 0, updated_at = NOW()
            WHERE status = 1 AND end_datetime < NOW()
        ");

        // Log the update
        Log::info("Guide trip statuses updated: {$count}");

        // Output the result in CLI
        $this->info("Updated {$count} guide trips.");
    }
}
