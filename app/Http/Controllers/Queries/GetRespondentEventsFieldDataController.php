<?php

namespace App\Http\Controllers\Queries;

use App\Models\ProjectData;
use App\Models\ProjectMetadata;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;


class GetRespondentEventsFieldDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId, string $record, string $field_name)
    {

        $metadata = ProjectMetadata::where([
            'project_id' => $projectId,
            'field_name' => $field_name,
        ])->get()->map(fn($project_metadata) => [
            'project_id' => $project_metadata->project_id,
            'field_name' => $project_metadata->field_name,
            'form_name' => $project_metadata->form_name,
            'form_menu_description' => $project_metadata->form_menu_description,
            'element_label' => $project_metadata->element_label,
            'element_enum' => $project_metadata->element_enum,
            'element_note' => $project_metadata->element_note,
        ]);

        $data = ProjectData::where([
            'project_id' => $projectId,
            'field_name' => $field_name,
            'record' => $record,
        ])->addSelect([
            'event_name' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
        ])->get();
    
        return response()->json(['fieldEventsData' => $data, 'fieldMetadata' => $metadata], 200);
    }
    
}
