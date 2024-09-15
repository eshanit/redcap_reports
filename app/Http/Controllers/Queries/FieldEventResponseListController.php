<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class FieldEventResponseListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId,string $eventId, string $fieldName,string $value)
    {
        //
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $records = QueryBuilder::for(ProjectData::class)
            ->with(['project_event_metadata', 'projects.project_metadata'])
            ->allowedFilters(['status', 'search'])
            ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
            ->where('project_id', $projectId)
            ->where('event_id', $eventId)
            ->where('field_name', $fieldName)
            ->where('value', $value)
            ->get()
            ->map(fn($record) => [
                'record' => $record->record,
                'event' => [
                    'id' => $record->event_id,
                    'name' =>$record->project_event_metadata->descrip,
                ],
                'field_name' => $record->field_name,
                'value' => $record->value,
                'form_name' => $record->projects->project_metadata->first()->form_name
            ]);

        return Inertia::render('Data/List', [
            'filters' => Request::all('search', 'sort'),
            'records' => $records,
            'project_id' => $projectId,
            'field_name' => $fieldName,
            'value' => $value,
            'project' => $project
        ]);

    }
}
