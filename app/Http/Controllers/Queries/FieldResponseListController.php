<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class FieldResponseListController extends Controller
{
    /** 
     * For a particular survey field = $fieldName select data for all respondents who gave an answer = $value
     */
    public function __invoke(string $projectId, string $fieldName, string $value)
    {
        //
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $records = QueryBuilder::for(ProjectData::class)
            ->with(['project_event_metadata', 'projects.project_metadata'])
            ->allowedFilters(['status', 'search'])
            ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
            ->where('project_id', $projectId)
            ->where('field_name', $fieldName)
            ->where('value', $value)
            ->get()
            ->map(fn($project) => [
                'record' => $project->record,
                'event' => [
                    'id' => $project->event_id,
                    'name' =>$project->project_event_metadata->descrip,
                ],
                'field_name' => $project->field_name,
                'value' => $project->value,
                'form_name' => $project->projects->project_metadata->first()->form_name,
                // 'units' => $project->projects->project_metadata->first()->field_units,
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
