<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class UnionFilteredQueryController extends Controller
{
    public function __invoke(string $projectId, string $type, InputRequest $request)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $qArray = collect($request->all())->map(function ($el) use ($projectId) {


            return QueryBuilder::for(ProjectData::class)
                ->with(['project_event_metadata', 'projects.project_metadata'])
                ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
                ->where('project_id', $projectId)
                ->where('event_id', $el['event_id'])
                ->where('field_name', $el['field_name'])
                ->when($el['operator'] === 'BETWEEN', function ($query) use ($el) {
                    return $query->whereBetween('value', [$el['values'][0]['min'], $el['values'][1]['max']]);
                }, function ($query) use ($el) {
                    if ($el['operator'] === 'OR' || $el['operator'] === '=') {
                        return $query->whereIn('value', $el['values']);
                    } else if ($el['operator'] === 'LIKE'){
                        return $query->where('value','like', $el['values'][0].'%');
                    } else {
                        return $query->where('value', $el['operator'], $el['values'][0]);
                    }
                });
        })->toArray();

        // Initialize a variable to hold the base query
        $baseQuery = array_shift($qArray); // Get the first query from the array

        // Loop through the remaining queries and apply unionAll
        if ($type = 'unionAll') {
            foreach ($qArray as $query) {
                $baseQuery = $baseQuery->unionAll($query);
            }
        } else {
            foreach ($qArray as $query) {
                $baseQuery = $baseQuery->union($query);
            }
        }

        //dd($baseQuery->count());
        // Apply filters and paginate
        $records = $baseQuery
            ->paginate($baseQuery->count())
            ->withQueryString($baseQuery->count() + 5)
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


        return Inertia::render('Data/UnPaginatedList', [
            'filters' => Request::all('search', 'sort'),
            'records' => $records,
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
        ]);
    }
}
