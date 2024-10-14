<?php

namespace App\Http\Controllers\Queries;

ini_set('max_execution_time', -1);

use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as InputRequest;
use Inertia\Inertia;

class IntersectionFilteredQueryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, string $type, InputRequest $request)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
        $query = ProjectData::where('project_id', $projectId)->addSelect([
            'event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data3.event_id')
        ]);

        $intersectionResults = [];
        foreach ($request->except('page') as $condition) {
            $intersectionResults[] = $this->applyCondition(clone $query, $condition)->get()->toArray();
        }

      //  dd($intersectionResults[0][0]);

        $intersectedResults = $this->intersectArraysByRecord(...$intersectionResults);
        //dd( $intersectedResults);


        $path = $type === 'records' ? 'Data/Intersections/RecordsOnly' : 'Data/Test2';

        return Inertia::render($path, [
            'requests' => $request->except('page'),
            'records' => $intersectedResults,
            'project_id' => $projectId,
            'field_name' => [],
            'value' => [],
            'project' => $project,
            'selected' => array_keys($intersectedResults)
        ]);
    }

    protected function intersectArraysByRecord(...$arrays)
    {
        if (empty($arrays)) {
            return [];
        }

        //dd($arrays[0]);
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
        $condition['values'] = array_map(function ($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $condition['values']);

        $query->where('field_name', $condition['field_name']);

        if ($condition['event_id'] == 0) {


            if ($condition['operator'] != 'ALL') {
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
            } else {
                $query->where('value', '!=', null);
            }
        } else {

            if ($condition['operator'] != 'ALL') {
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
            } else {
                $query->where('value', '!=', null);
            }

            $query->where('event_id', $condition['event_id']);
        }
        // dd($query->toSQL());
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
