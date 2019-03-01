<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Exports\TimeEntriesExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TimeEntriesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $timeEntries = [];
        $currentDate = $project = $description = '';

        foreach ($rows as $row) {
            $startTime = $this->timeToFullHour(Date::excelToDateTimeObject($row['start_time']));
            $endTime = $this->timeToFullHour(Date::excelToDateTimeObject($row['end_time']));

            $duration = new Carbon($endTime);
            $duration = $duration->diff(new Carbon($startTime))->format('%H:%I:%S');

            if ($row['date']) {
                $currentDate = Date::excelToDateTimeObject($row['date'])->format('Y-m-d');
            }

            if ($row['project']) {
                if ($row['project'] == 'Breaks') {
                    continue;
                }

                $project = $row['project'];
            }

            if ($row['description']) {
                $description = $row['description'];
            }

            array_push($timeEntries, [
                'user' => config('app.user.name'),
                'email' => config('app.user.email'),
                'client' => '',
                'project' => $project,
                'task' => '',
                'description' => $description,
                'billable' => 'No',
                'start_date' => $currentDate,
                'start_time' => $startTime,
                'end_date' => $currentDate,
                'end_time' => $endTime,
                'duration' => $duration,
                'tag' => '',
                'amount' => '',
            ]);
        }

        return Excel::store(new TimeEntriesExport($timeEntries), 'download.csv');
    }

    /**
     * Make time to the 24 hours based
     *
     * @param DateTime $date
     * @return string time
     **/
    public function timeToFullHour($date)
    {
        $date = $date->format('h:i:s');
        $hour = substr($date, 0, 5);

        return date("H:i:s", strtotime($hour >= 9 && $hour < 12 ? $date .= ' AM' : $date .= ' PM'));
    }
}
