<?php
namespace App\Http\Controllers\Queries;

use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;

class GetEventsControllerEventsBased extends Controller
{
    public function __invoke(string $projectId, string $fieldName)
    {
        $events = ProjectData::where('project_id', $projectId)
            ->where('field_name', $fieldName)
            ->addSelect([
                'id' => 'redcap_data.event_id',
                'name' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
            ])->distinct()->get();

        //dd($events);

        return response()->json(['fieldEvents' => $events],200);
    }
}
