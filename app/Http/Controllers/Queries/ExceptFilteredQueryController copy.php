<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class ExceptFilteredQueryController extends Controller
{
    public function __invoke(string $projectId, InputRequest $request)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $el = $request->all();

        $el[0]['values'] = array_map(function($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $el[0]['values']);

        $queryA = ProjectData::where('project_id', $projectId)
        ->where('event_id', $el[0]['event_id'])
        ->where('field_name', $el[0]['field_name'])
        ->when($el[0]['operator'] === 'BETWEEN', function ($query) use ($el) {
            return $query->whereBetween('value', [$el[0]['values'][0]['min'], $el[0]['values'][1]['max']]);
        }, function ($query) use ($el) {
            if ($el[0]['operator'] === 'OR' || $el[0]['operator'] === '=') {
                return $query->whereIn('value', $el[0]['values']);
            } else if ($el[0]['operator'] === 'LIKE'){
                return $query->where('value','like', $el[0]['values'][0].'%');
            } else {
                return $query->where('value', $el[0]['operator'], $el[0]['values'][0]);
            }
        })->get();

        $el[1]['values'] = array_map(function($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $el[1]['values']);

        $queryB = ProjectData::where('project_id', $projectId)
        ->where('event_id', $el[1]['event_id'])
        ->where('field_name', $el[1]['field_name'])
        ->when($el[1]['operator'] === 'BETWEEN', function ($query) use ($el) {
            return $query->whereBetween('value', [$el[1]['values'][0]['min'], $el[1]['values'][1]['max']]);
        }, function ($query) use ($el) {
            if ($el[1]['operator'] === 'OR' || $el[1]['operator'] === '=') {
                return $query->whereIn('value', $el[1]['values']);
            } else if ($el[1]['operator'] === 'LIKE'){
                return $query->where('value','like', $el[1]['values'][0].'%');
            } else {
                return $query->where('value', $el[1]['operator'], $el[1]['values'][0]);
            }
        })->get();

        $final = $queryA->diff($queryB);

        dd($final);

        return Inertia::render('Data/UnPaginatedList', [
            'filters' => Request::all('search', 'sort'),
            'records' => [],
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
        ]);
    }
}
