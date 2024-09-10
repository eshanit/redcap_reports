<?php

namespace App\Http\Controllers\Queries;

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Inertia\Inertia;

class IntersectionFilteredQueryCopy2Controller extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, string $type, InputRequest $request)
    {
        // Fetch the project details
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
    
        // Build the query for ProjectData
        $query = ProjectData::where('project_id', $projectId)->addSelect([
            'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data.event_id')
        ]);
    
        $intersectionResults = [];
        foreach ($request->except('page') as $condition) {
            // Get the results as an array
            $intersectionResults[] = $this->applyCondition(clone $query, $condition)->get()->toArray();
        }
    
        // Intersect the results based on records
        $intersectedResults = $this->intersectArraysByRecord(...$intersectionResults);
    
        // Pagination logic
        $perPage = 1000; // Set the number of items per page
        $currentPage = $request->all(); // Get the current page from the request
        dd($currentPage, url());
        $paginatedResults = array_slice($intersectedResults, ($currentPage - 1) * $perPage, $perPage);
    
        // Create a pagination structure
        $total = count($intersectedResults);
        $pagination = [
            'current_page' => $currentPage,
            'last_page' => (int)ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
            'links' => [] // Placeholder for links, to be filled later
        ];
    
        // Generate pagination links
        if ($total > 0) {
            for ($page = 1; $page <= $pagination['last_page']; $page++) {
                $pagination['links'][] = [
                    'label' => (string)$page,
                    'url' => $page === $currentPage ? null : url()->current() . '?page=' . $page,
                    'active' => $page === $currentPage,
                ];
            }
        }
    
        // Determine the path based on type
        $path = $type === 'records' ? 'Data/Intersections/RecordsOnly' : 'Data/Test2';
    
        return Inertia::render($path, [
            'filters' => Request::all('search', 'sort'),
            'requests' => $request->except('page'),
            'records' => $paginatedResults,
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
            'selected' => array_keys($paginatedResults), // Adjust if needed
            'pagination' => $pagination, // Add pagination data
        ]);
    }

    protected function intersectArraysByRecord(...$arrays) {
        if (empty($arrays)) {
            return [];
        }
    
        // Step 1: Extract records from the first array
        $records = array_column($arrays[0], 'record');
    
        // Step 2: Iterate through the remaining arrays to find intersections
        foreach ($arrays as $array) {
            $currentRecords = array_column($array, 'record');
            $records = array_intersect($records, $currentRecords);
        }
    
        // Step 3: Filter original arrays to get complete entries
        $intersectedResults = [];
        foreach ($arrays as $array) {
            $intersectedResults = array_merge($intersectedResults, array_filter($array, function ($item) use ($records) {
                return in_array($item['record'], $records);
            }));
        }
    
        return $intersectedResults;
    }

    protected function applyCondition($query, $condition)
    {
        return $this->applyGenericCondition($query, $condition);
    }

    protected function applyConditionOnRecord($query, $condition, $record)
    {
        $query->where('record', $record);
        return $this->applyGenericCondition($query, $condition);
    }

    protected function applyGenericCondition($query, $condition)
    {
        $condition['values'] = array_map(function($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $condition['values']);

        $query->where('field_name', $condition['field_name']);

        switch ($condition['operator']) {
            case 'OR':
                $query->whereIn('value', $condition['values']);
                break;
            case 'BETWEEN':
                $query->whereBetween('value', [$condition['values'][0]['min'], $condition['values'][1]['max']]);
                break;
            default:
                $query->where('value', $condition['operator'], $condition['values'][0]);
        }

        return $query;
    }

    protected function getRecordsData($conditions, $selectedRecords, $projectId)
    {
        $recordsData = [];

        foreach ($conditions as $condition) {
            foreach ($selectedRecords as $record) {
                $recordsData[] = $this->applyConditionOnRecord(clone ProjectData::where('project_id', $projectId), $condition, $record)->get();
            }
        }

        return $recordsData;
    }
}