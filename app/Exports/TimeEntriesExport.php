<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimeEntriesExport implements FromCollection, WithHeadings
{
    protected $timeEntries;

    public function __construct($timeEntries)
    {
        $this->timeEntries = $timeEntries;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->timeEntries);
    }

    public function headings(): array
    {
        return [
            'User',
            'Email',
            'Client',
            'Project',
            'Task',
            'Description',
            'Billable',
            'Start date',
            'Start time',
            'End date',
            'End time',
            'Duration',
            'Tags',
            'Amount ()',
        ];
    }
}
