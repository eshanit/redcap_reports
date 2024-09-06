<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use Illuminate\Support\Facades\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IntersectionFilteredQueryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId,string $type, InputRequest $request)
    {
        //
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $query = ProjectData::where('project_id', $projectId);

        // $intersectionResults = []; // Initialize the array

        // $filterRequest = Request::all('search', 'sort');

        // $inputRequest = $request->except('page');

        //     $inputRequestWithExtras = array_map(function ($item) use ($filterRequest) {
        //         return array_merge($item, $filterRequest);
        //     }, $inputRequest);



        // dd($inputRequestWithExtras);

        // dd( Request::all('search', 'sort'), $request->all());
       // dd($request->except('page'));
        foreach ($request->except('page') as $index => $condition) {
            //dd($condition);
            $intersectionResults[$index] = $this->applyCondition(clone $query, $condition)->get()->pluck('record')->toArray();
        }

       // dd($intersectionResults);

        $selectedRecords = array_intersect(...$intersectionResults);

       // dd($selectedRecords);

        if( $type == 'records') {

            $data = $selectedRecords;

            $path = 'Data/Intersections/RecordsOnly';

        } else {

            
        // $data = QueryBuilder::for(ProjectData::class)
        //     ->with(['project_event_metadata', 'projects.project_metadata'])
        //     ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
        //     ->where('project_id', $projectId)
        //     ->whereIn('record', $selectedRecords)
        //     ->get()
        //     ->map(fn($project) => [
        //         'record' => $project->record,
        //         'event' => [
        //             'id' => $project->event_id,
        //             'name' => $project->project_event_metadata->descrip,
        //         ],
        //         'field_name' => $project->field_name,
        //         'value' => $project->value,
        //         'form_name' => $project->projects->project_metadata->first()->form_name,
        //     ]);

        $data = QueryBuilder::for(ProjectData::class)
        ->with(['project_event_metadata', 'projects.project_metadata'])
        ->allowedSorts(['record', 'event_id', 'field_name', 'value', 'form_name'])
        ->where('project_id', $projectId)
        ->whereIn('record', $selectedRecords)
        ->filter(Request::only('search', 'trashed'))
        ->paginate(1000)
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

           // $path = 'Data/Intersections/FullRecords';


           $path = 'Data/Test';

        }


          //dd($allRecords);

        return Inertia::render($path, [
            'filters' => Request::all('search', 'sort'),
            'requests' => $request->except('page'),
            'records' => $data,//$allRecords->groupBy(['event.id','record'])->flatten(1),
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
        ]);
    }

    protected function applyCondition($query, $condition)
    {

        //  dd($condition);
        $field = $condition['field_name'];
        $eventId = $condition['event_id'];
        $operator = $condition['operator'];
        $values = $condition['values'];

        //$query->where('event_id', $eventId);
        $query->where('field_name', $field);

        switch ($operator) {
            case 'OR':
                $query->whereIn('value', $values);
                break;
            case 'BETWEEN':
                $query->whereBetween('value', [$values[0]['min'], $values[1]['max']]);
                break;
                // Add more cases for other operators if needed
            default:
                $query->where('value', $operator, $values[0]);
        }

        // // dd($query->getBindings(), $query->toSql());
        // Handle sorting
      //  dd($condition);
        // if ($condition['sort'] !== null) {
        //     $sortField = $condition['sort'];
        //     $sortDirection = $condition['sort_direction'] ?? 'asc';
        //     $query->orderBy($sortField, $sortDirection);
        // }

        return  $query;
    }
}
