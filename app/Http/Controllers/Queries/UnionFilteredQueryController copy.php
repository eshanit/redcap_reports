<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class UnionFilteredQueryControllerCopy extends Controller
{
    public function __invoke(string $projectId, string $type, InputRequest $request)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $qArray = collect($request->all())->map(function ($el) use ($projectId) {
            return $this->buildConditionQuery($projectId, $el);
        })->toArray();

        // Initialize a variable to hold the base query
        $baseQuery = array_shift($qArray); // Get the first query from the array

        // Loop through the remaining queries and apply unionAll
        foreach ($qArray as $query) {
            $baseQuery = $baseQuery->unionAll($query);
        }

        // Convert the base query to a Spatie Query Builder
        $spatieQueryBuilder = QueryBuilder::for(ProjectData::class)
            ->with(['project_event_metadata', 'projects.project_metadata'])
            ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name']);

        // Apply the union query
        $spatieQueryBuilder->getQuery()->fromSub($baseQuery, 'subquery');

        // Apply filters and paginate
        $records = $spatieQueryBuilder
            ->filter(Request::only('search', 'trashed'))
            ->paginate(25)
            ->withQueryString()
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

        dd($records);

        return Inertia::render('Data/List', [
            'filters' => Request::all('search', 'sort'),
            'records' => $records,
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
        ]);
    }

    protected function buildConditionQuery($projectId, $el)
    {
        return QueryBuilder::for(ProjectData::class)
            ->with(['project_event_metadata', 'projects.project_metadata'])
            ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
            ->where('project_id', $projectId)
            ->where('event_id', $el['event_id'])
            ->where('field_name', $el['field_name'])
            ->when($el['operator'] === 'BETWEEN', function ($query) use ($el) {
                return $query->whereBetween('value', [$el['values'][0]['min'], $el['values'][1]['max']]);
            }, function ($query) use ($el) {
                return $query->whereIn('value', $el['values']);
            })
            ->getQuery();
    }
}
