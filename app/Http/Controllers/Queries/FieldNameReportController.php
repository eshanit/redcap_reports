<?php

namespace App\Http\Controllers\Queries;

use App\Models\ProjectData;
use App\Models\ProjectEventMetadata;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class FieldNameReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, string $fieldName)
    {
        //
        $reportData = QueryBuilder::for(ProjectData::class)->where('project_id', $projectId)
            ->where('field_name', $fieldName)
            ->with('projects', function ($project) use ($fieldName) {
                return $project->with('project_metadata', function ($query) use ($fieldName) {
                    $query->where('field_name', $fieldName)->first();
                });
            })
            ->addSelect(['event' => ProjectEventMetadata::select('descrip')->whereColumn('event_id', 'redcap_data3.event_id')->take(1)]);

        $mapEventToId = $reportData->get()->map(fn($data) => [
            'event_id' => $data->event_id,
            'event' => $data->event
        ])->unique(function ($item) {
            return $item['event_id'];
        })->values()->toArray();

        $values = $reportData->get()->map(
            fn($data) =>
            $data->value
        )->toArray();

        // $valueCountsByEvent = $reportData->get()->groupBy('event')->map(
        //     fn($data) =>
        //     $data->map(fn($project) => $project->value)->countBy()
        // )->sortDesc()->toArray();

        $valueCountsByEventId = $reportData->get()->groupBy('event_id')->map(
            fn($data) =>
            $data->map(fn($project) => $project->value)->countBy()
        )->sortDesc()->toArray();

       // dd($valueCountsByEvent, $valueCountsByEventId,  $mapEventToId);

        //dd($valueCountsByEvent, $valueCountsByEventId);

        return Inertia::render(
            'Reports/Show',
            [
                'reportData' => $reportData->paginate(25)
                    ->withQueryString()
                    ->through(fn($project) => [
                        'project_id' => $project->project_id,
                        'record' => $project->record,
                        'event_id' => $project->event_id,
                        'event' => $project->event,
                        'field_name' => $project->field_name,
                        'value' => $project->value,
                        'app_title' => $project->projects->app_title,
                        'element_lable' => $project->projects->project_metadata->first()->element_label,
                        'form_name' => $project->projects->project_metadata->first()->form_name,
                        'form_menu_description' => $project->projects->project_metadata->first()->form_menu_description,
                        'field_units' => $project->projects->project_metadata->first()->field_units,
                        'element_type' => $project->projects->project_metadata->first()->element_type,
                        'element_note' => $project->projects->project_metadata->first()->element_note,
                        'element_enum' => $project->projects->project_metadata->first()->element_enum,
                        'element_validation_type' => $project->projects->project_metadata->first()->element_validation_type,
                        'element_validation_min' => $project->projects->project_metadata->first()->element_validation_min,
                        'element_validation_max' => $project->projects->project_metadata->first()->element_validation_max
                    ]),
                'valueCounts' => array_count_values($values),
                'values' => $values,
                'mapEventToId' => $mapEventToId,
                'valueCountsByEventId' => $valueCountsByEventId
            ]

        );
    }
}
