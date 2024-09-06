<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectMetadata;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class RepondentRecordTrackingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, $recordId)
    {
        //
        $project = Project::findOrfail($projectId);

        $metadata = ProjectMetadata::getFieldNames($projectId)->get();

        return Inertia::render('Respondents/RecordEventTracking',  [
            'project' => $project,
            'recordId' => $recordId,
            'metadata' => $metadata
           ]);
        
    }
}
