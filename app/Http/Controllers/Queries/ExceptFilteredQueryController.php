<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Illuminate\Http\Request as InputRequest;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class ExceptFilteredQueryController extends Controller
{
    public function __invoke(string $projectId, InputRequest $request)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
        $el = $request->all();

        // Validate request data (considering you have custom validation logic)
        $this->validateRequest($el);

        // Process both queries
        $queryA = $this->processQuery($projectId, $el[0]);
        $queryB = $this->processQuery($projectId, $el[1]);

        // Get the difference
        $final = $queryA->diff($queryB);

        dd($el, $final, $queryA , $queryB);

        return Inertia::render('Data/UnPaginatedList', [
            'filters' => $request->all('search', 'sort'),
            'records' => $final,
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
        ]);
    }

    private function processQuery(string $projectId, array $filter)
    {
        $filter['values'] = array_map(function($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $filter['values']);

        return ProjectData::where('project_id', $projectId)
            //->where('event_id', $filter['event_id'])
            ->where('field_name', $filter['field_name'])
            ->when($filter['operator'] === 'BETWEEN', function ($query) use ($filter) {
                return $query->whereBetween('value', [$filter['values'][0]['min'], $filter['values'][1]['max']]);
            }, function ($query) use ($filter) {
                if ($filter['operator'] === 'OR' || $filter['operator'] === '=') {
                    return $query->whereIn('value', $filter['values']);
                } elseif ($filter['operator'] === 'LIKE') {
                    return $query->where('value', 'like', $filter['values'][0] . '%');
                } else {
                    return $query->where('value', $filter['operator'], $filter['values'][0]);
                }
            })->get();
    }

    private function validateRequest(array $data)
    {
        // Implement your validation logic here
    }
}