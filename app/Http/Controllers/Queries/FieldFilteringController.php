<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectMetadata;
use App\Http\Controllers\Controller;
use Inertia\Inertia;


class FieldFilteringController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId)
    {

        $project = Project::findOrfail($projectId);

        $metadata = ProjectMetadata::getFieldNames($projectId)->get();

    
        return Inertia::render('Data/Filteringv2', [
            'project' => $project,
            'fieldEvents' => [],
            'metadata' => $metadata,
            'metadataByField' => $metadata->groupBy('field_name')
        ]);
    }
}
