<?php
namespace App\Http\Controllers\Queries;

use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use App\Http\Controllers\Controller;

class GetEventsController extends Controller
{
    public function __invoke(string $projectId, string $fieldName)
    {
        $events = ProjectData::where('project_id', $projectId)
            ->where('field_name', $fieldName)
            ->distinct()
            ->pluck('instance')->map(function ($instance) {

                $instancePosition = $instance ?? 1;

                return [
                    'id' => $instance,
                    'name' => 'Visit - '.$instancePosition
                ];
            });

      // dd($events);

        return response()->json(['fieldEvents' => $events->toArray()],200);
    }
}
