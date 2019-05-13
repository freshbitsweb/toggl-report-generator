<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Imports\TimeEntriesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TogglTimeEntryPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $baseFilePath = base_path() . '/../../desktop_files/file.ods';

        $fileName = now()->toDateTimeString();

        Storage::disk('backup')->put($fileName.'.ods', file_get_contents($baseFilePath));

        Excel::import(new TimeEntriesImport, 'backup/'.$fileName.'.ods');

        unlink($baseFilePath);

        copy(storage_path('app/file_format/file.ods'), $baseFilePath);
    }
}
