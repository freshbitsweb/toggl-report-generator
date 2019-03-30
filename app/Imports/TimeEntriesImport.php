<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Services\Toggl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

            $duration = (new Carbon($endTime))->diffInSeconds(new Carbon($startTime));

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
                'project' => $project,
                'description' => $description,
                'start' => Carbon::createFromFormat('Y-m-d H:i:s', $currentDate.' '.$startTime)->format('c'),
                'end' => Carbon::createFromFormat('Y-m-d H:i:s', $currentDate.' '.$endTime)->format('c'),
                'duration' => $duration,
            ]);
        }

        $toggl = resolve(Toggl::class);
        $workspaceId = $toggl->getUserWorkspaceId();
        $this->projects = $toggl->getProjectsOf($workspaceId);

        foreach ($timeEntries as $timeEntry) {
            $timeEntryDetails = [
                'description' => $timeEntry['description'],
                'wid' => $workspaceId,
                'pid' => $this->getProjectId($timeEntry['project']),
                'start' => $timeEntry['start'],
                'stop' => $timeEntry['end'],
                'duration' => $timeEntry['duration'],
                'created_with' => 'Laravel Toggl Report Generator',
            ];

            $timeEntryResponse = $toggl->addNewTimeEntry($timeEntryDetails);

            if (property_exists($timeEntryResponse, 'success')) {
                Log::channel('toggl_error')->info(collect([$timeEntryResponse, $timeEntryDetails])->toJson());
                continue;
            }

            Log::channel('toggl_success')->info(collect([$timeEntryResponse, $timeEntryDetails])->toJson());
        }

        return;
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

    /**
     * Returns the toggl project id from project name
     *
     * @param string $projectName
     * @return int
     **/
    public function getProjectId($projectName)
    {
        $projects = collect($this->projects);

        return $projects->firstWhere('name', $projectName)->id;
    }
}
