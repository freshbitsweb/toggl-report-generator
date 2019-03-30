<?php

namespace App\Services;

use MorningTrain\TogglApi\TogglApi;

class Toggl
{
    protected $toggl;

    public function __construct()
    {
        $this->toggl = new TogglApi(config('services.toggl.key'));
    }

    /**
     * Fetch and returns the workspace id
     *
     * @return int
     **/
    public function getUserWorkspaceId()
    {
        return collect($this->toggl->getWorkspaces())
            ->firstWhere('name', config('services.toggl.workspace'))
            ->id
        ;
    }

    /**
     * Fetch and returns the list of all projects
     *
     * @param int $workspaceId
     * @return stdClass
     **/
    public function getProjectsOf($workspaceId)
    {
        return $this->toggl->getWorkspaceProjects($workspaceId);
    }

    /**
     * Creates a new time entry in toggl
     *
     * @param array $timeEntryDetails
     * @return stdClass
     **/
    public function addNewTimeEntry($timeEntryDetails)
    {
        return $this->toggl->createTimeEntry($timeEntryDetails);
    }
}
