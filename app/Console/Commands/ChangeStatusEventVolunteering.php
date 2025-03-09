<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Volunteering;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChangeStatusEventVolunteering extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-status-event-volunteering';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of events and volunteering records when they expire.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $updatedEvents = Event::where('status', '1')
            ->where('end_datetime', '<', $now)
            ->update(['status' => '0']);

        $updatedVolunteerings = Volunteering::where('status', '1')
            ->where('end_datetime', '<', $now)
            ->update(['status' => '0']);

        // Log the changes
        Log::info("Event statuses updated: {$updatedEvents}");
        Log::info("Volunteering statuses updated: {$updatedVolunteerings}");
    }
}
