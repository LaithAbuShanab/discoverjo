<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChangeTripStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-trip-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of trips when they expire.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $updatedTrips = Trip::where('status', '1')
            ->where('date_time', '<', $now)
            ->update(['status' => '0']);

        // Log the changes
        Log::info("Trip statuses updated: {$updatedTrips}");

        // Output for CLI
        $this->info("Updated {$updatedTrips} trips.");
    }
}
