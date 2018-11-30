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
        Excel::import(new TimeEntriesImport, 'public/file.ods');

        return response()->download(storage_path('app/download.csv'));
    }
}
