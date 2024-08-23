<?php

namespace App\Http\Controllers\Queries;

use App\Models\ProjectMetadata;
use App\Models\Project;
use App\Http\Controllers\Controller;
use App\Models\ProjectData;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FieldFilteringController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId)
    {

        $project = Project::findOrfail($projectId);

        $metadata = ProjectMetadata::where('project_id', $projectId)
            ->select(
                'field_name',
                'form_name',
                'element_type',
                'element_enum',
                'element_validation_type',
                'element_validation_min',
                'element_validation_max'
            )
            ->get();


        $fieldEvents = ProjectData::select('redcap_data.field_name', 'redcap_data.event_id', 'redcap_events_metadata.descrip')
            ->join('redcap_events_metadata', 'redcap_data.event_id', '=', 'redcap_events_metadata.event_id')
            ->where('redcap_data.project_id', $projectId)
            ->distinct()
            ->get()
            ->groupBy('field_name');
            

        // $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);


        return Inertia::render('Data/Filtering', [
            'project' => $project,
            'fieldEvents' => $fieldEvents,
            'metadata' => $metadata,
            'metadataByField' => $metadata->groupBy('field_name')
        ]);
    }
}
