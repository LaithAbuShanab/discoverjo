<?php

namespace App\Console\Commands;

use App\Models\DeleteCounter;
use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;


class CheckDeleteCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-delete-counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thresholdDate = Carbon::now()->subWeeks(2);

        $deletedCounters = DeleteCounter::where('deleted_count', 3)
            ->where('updated_at', '<', $thresholdDate)
            ->get();

        foreach ($deletedCounters as $counter) {
            $user = User::find($counter->user_id);
            if ($user) {
                $user->status = 1;
                $user->save();
            }

            $counter->delete();
        }

        $this->info('User statuses updated successfully.');
    }
}
