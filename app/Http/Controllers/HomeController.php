<?php

namespace App\Http\Controllers;

use App\Imports\TimeEntriesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * index page
     *
     * @return file
     **/
    public function index()
    {
        $baseFilePath = base_path() . '/../../desktop_files/file.ods';

        $fileName = now()->toDateTimeString();

        Storage::disk('backup')->put($fileName.'.ods', file_get_contents($baseFilePath));

        Excel::import(new TimeEntriesImport, 'backup/'.$fileName.'.ods');

        unlink($baseFilePath);

        copy(storage_path('app/file_format/file.ods'), $baseFilePath);
    }

    /**
     * Display upload time entry page
     *
     * @return \Illuminate\Http\Response
     **/
    public function timeEntries()
    {
        return view('time_entries');
    }

    /**
     * Upload time entries to toggle
     *
     * @return \Illuminate\Http\RedirectResponse
     **/
    public function uploadTimeEntries()
    {
        request()->validate([
            'time_entry' => 'required|file'
        ]);

        $fileName = now()->toDateTimeString();

        Storage::disk('backup')->put($fileName.'.ods', file_get_contents(request()->file('time_entry')));

        Excel::import(new TimeEntriesImport, 'backup/'.$fileName.'.ods');

        return back()->with([
            'message' => 'Time Entries Uploaded successfully'
        ]);
    }
}
