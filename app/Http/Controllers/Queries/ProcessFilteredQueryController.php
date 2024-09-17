<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Illuminate\Support\Facades\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Queries\ProcessFilterQueryRequest;
use Inertia\Inertia;


class ProcessFilteredQueryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, ProcessFilterQueryRequest $request)
    {

        //
        $queryData = $request->validated();
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $queryData['values'] = array_map(function ($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $queryData['values']);



        if ($queryData['operator'] == 'ALL') {

            if ($queryData['event_id'] == 0) {

                $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field from all events";

                $records = QueryBuilder::for(ProjectData::class)
                    ->with(['project_event_metadata', 'projects.project_metadata'])
                    ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
                    ->where('project_id', $projectId)
                    ->where('field_name', $queryData['field_name'])
                    ->get()
                    ->map(fn($project) => [
                        'record' => $project->record,
                        'event' => [
                            'id' => $project->event_id,
                            'name' => $project->project_event_metadata->descrip,
                        ],
                        'field_name' => $project->field_name,
                        'value' => $project->value,
                        'form_name' => $project->projects->project_metadata->first()->form_name,
                    ]);
            } else {

                $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field.These values are 
                only from event " . $queryData['event_name'];

                $records = QueryBuilder::for(ProjectData::class)
                    ->with(['project_event_metadata', 'projects.project_metadata'])
                    ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
                    ->where('project_id', $projectId)
                    ->where('event_id', $queryData['event_id'])
                    ->where('field_name', $queryData['field_name'])
                    ->get()
                    ->map(fn($project) => [
                        'record' => $project->record,
                        'event' => [
                            'id' => $project->event_id,
                            'name' => $project->project_event_metadata->descrip,
                        ],
                        'field_name' => $project->field_name,
                        'value' => $project->value,
                        'form_name' => $project->projects->project_metadata->first()->form_name,
                    ]);
            }
        } else {

            if ($queryData['event_id'] == 0) {

                if ($queryData['operator'] === 'BETWEEN') {
                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which are " . $queryData['operator'] . " " . $queryData['values'][0]['min'] . " and " . $queryData['values'][0]['max'] . "These values are 
                    only from all events";
                } elseif ($queryData['operator'] === 'LIKE') {
                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which contains the text " . $queryData['values'][0] . ".These values are 
                    only from all events";
                } else {
                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which are " . $queryData['operator'] . " " . $queryData['values'][0] . ".These values are 
                    only from all events";
                }


                $records = QueryBuilder::for(ProjectData::class)
                    ->with(['project_event_metadata', 'projects.project_metadata'])
                    ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
                    ->where('project_id', $projectId)
                    ->where('field_name', $queryData['field_name'])
                    ->when($queryData['operator'] === 'BETWEEN', function ($query) use ($queryData) {
                        return $query->whereBetween('value', [$queryData['values'][0]['min'], $queryData['values'][1]['max']]);
                    }, function ($query) use ($queryData) {
                        // return $query->whereIn('value', $queryData['values']);
                        if ($queryData['operator'] === 'OR' || $queryData['operator'] === '=') {
                            return $query->whereIn('value', $queryData['values']);
                        } else if ($queryData['operator'] === 'LIKE') {
                            return $query->where('value', 'like', $queryData['values'][0] . '%');
                        } else {
                            // dd($queryData['values']);
                            return $query->where('value', $queryData['operator'], $queryData['values'][0]);
                        }
                    })
                    ->get()
                    ->map(fn($project) => [
                        'record' => $project->record,
                        'event' => [
                            'id' => $project->event_id,
                            'name' => $project->project_event_metadata->descrip,
                        ],
                        'field_name' => $project->field_name,
                        'value' => $project->value,
                        'form_name' => $project->projects->project_metadata->first()->form_name,
                    ]);
            } else {

                if ($queryData['operator'] === 'BETWEEN') {
                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which are " . $queryData['operator'] . ". " . $queryData['values'][0]['min'] . " and " . $queryData['values'][0]['max'] . "These values are 
                    only from event " . $queryData['event_name'];
                } elseif ($queryData['operator'] === 'LIKE') {

                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which contains " . $queryData['values'][0] . ".These values are 
                only from event " . $queryData['event_name'];
                } else {

                    $queryDefinition = " The results are from field " . $queryData['field_name'] . " and we have pulled up all values for this field which are " . $queryData['operator'] . ". " . $queryData['values'][0] . ".These values are 
                only from event " . $queryData['event_name'];
                }

                $records = QueryBuilder::for(ProjectData::class)
                    ->with(['project_event_metadata', 'projects.project_metadata'])
                    ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
                    ->where('project_id', $projectId)
                    ->where('event_id', $queryData['event_id'])
                    ->where('field_name', $queryData['field_name'])
                    ->when($queryData['operator'] === 'BETWEEN', function ($query) use ($queryData) {
                        return $query->whereBetween('value', [$queryData['values'][0]['min'], $queryData['values'][1]['max']]);
                    }, function ($query) use ($queryData) {
                        // return $query->whereIn('value', $queryData['values']);
                        if ($queryData['operator'] === 'OR' || $queryData['operator'] === '=') {
                            return $query->whereIn('value', $queryData['values']);
                        } else if ($queryData['operator'] === 'LIKE') {
                            return $query->where('value', 'like', $queryData['values'][0] . '%');
                        } else {
                            return $query->where('value', $queryData['operator'], $queryData['values'][0]);
                        }
                    })
                    ->get()
                    ->map(fn($project) => [
                        'record' => $project->record,
                        'event' => [
                            'id' => $project->event_id,
                            'name' => $project->project_event_metadata->descrip,
                        ],
                        'field_name' => $project->field_name,
                        'value' => $project->value,
                        'form_name' => $project->projects->project_metadata->first()->form_name,

                    ]);
            }
        }

        //dd($records);


        //
        //dd($records->toSql(), $records->getBindings());
        // dd($queryData['values'], $projectId);
        return Inertia::render('Data/List', [
            'filters' => Request::all('search', 'sort'),
            'records' => $records,
            'project_id' => $projectId,
            'field_name' => $queryData['field_name'],
            'value' => $queryData['values'],
            'project' => $project,
            'queryDefinition' => $queryDefinition
        ]);
    }
}
