<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tour; // Import your model
use Carbon\Carbon;

class UpdateTourStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tour-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tour status at the end of each day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Example: update tours that ended today
        $affectedRows = Tour::whereDate('check_out_time', '<=', $today)
            ->where('tour_status', 'Definite')
            ->update(['tour_status' => 'Actual']);

        $this->info("{$affectedRows} tours updated to 'actual' status.");
    }
}
