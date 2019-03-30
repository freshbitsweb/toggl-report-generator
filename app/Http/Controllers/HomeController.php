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

        $fileName = now()->toDateString();

        Storage::disk('backup')->put($fileName.'.ods', file_get_contents($baseFilePath));

        Excel::import(new TimeEntriesImport, 'backup/'.$fileName.'.ods');

        unlink($baseFilePath);

        copy(storage_path('app/file_format/file.ods'), $baseFilePath);
    }
}
