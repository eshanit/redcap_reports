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
       // dd($request->all());
        //
        $queryData = $request->validated();
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        if($queryData['event_id'] == 0) {
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
                } else if ($queryData['operator'] === 'LIKE'){
                    return $query->where('value','like', $queryData['values'][0].'%');
                } else {
                    return $query->where('value', $queryData['operator'], $queryData['values'][0]);
                }
            })
            ->filter(Request::only('search', 'trashed'))
            ->paginate(25)
            ->withQueryString()
            ->through(fn($project) => [
                'record' => $project->record,
                'event' => [
                    'id' => $project->event_id,
                    'name' => $project->project_event_metadata->descrip,
                ],
                'field_name' => $project->field_name,
                'value' => $project->value,
                'form_name' => $project->projects->project_metadata->first()->form_name,
            ]);

        }else{

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
                } else if ($queryData['operator'] === 'LIKE'){
                    return $query->where('value','like', $queryData['values'][0].'%');
                } else {
                    return $query->where('value', $queryData['operator'], $queryData['values'][0]);
                }
            })
            ->filter(Request::only('search', 'trashed'))
            ->paginate(25)
            ->withQueryString()
            ->through(fn($project) => [
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

   
//
//dd($records->toSql(), $records->getBindings());
        // dd($queryData['values'], $projectId);
        return Inertia::render('Data/List', [
            'filters' => Request::all('search', 'sort'),
            'records' => $records,
            'project_id' => $projectId,
            'field_name' => $queryData['field_name'],
            'value' => $queryData['values'],
            'project' => $project
        ]);
    }
}
