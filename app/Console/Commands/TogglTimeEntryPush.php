<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TogglTimeEntryPushJob;

class TogglTimeEntryPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:time-entry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the time entries to the toggl';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dispatch_now(new TogglTimeEntryPushJob());

        $this->info("Time entry uploaded to toggl successfully.");
    }
}
