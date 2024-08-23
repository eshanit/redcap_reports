<?php

namespace App\Http\Controllers\Queries;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectData;
use App\Models\ProjectMetadata;
use Inertia\Inertia;

class FieldRecordContainingResponseController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $projectId, string $eventId, string $record)
    {
        //
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $records = ProjectData::query()
            ->where('project_id', $projectId)
            ->where('event_id', $eventId)
            ->where('record', $record)
            ->with([
                'project_event_metadata',
                'projects.project_metadata' => function ($query) {
                    $query->select(
                        'project_id',
                        'field_name',
                        'form_name',
                        'field_units',
                        'field_order',
                        'element_label',
                        'element_enum',
                        'element_note'
                    );
                }
            ])
            ->get()
            ->sortBy(function ($record) {
                $metadata = $record->projects->project_metadata->firstWhere('field_name', $record->field_name);
                return $metadata->field_order ?? 0;
            })
            ->values()
            ->map(function ($record) {
                $metadata = $record->projects->project_metadata->firstWhere('field_name', $record->field_name);
                return [
                    'project_id' => $record->project_id,
                    'field_name' => $record->field_name,
                    'value' => $record->value,
                    'event' => $record->project_event_metadata->descrip,
                    'form_name' => $metadata->form_name ?? null,
                    'field_units' => $metadata->field_units ?? null,
                    'field_order' => $metadata->field_order ?? null,
                    'element_label' => $metadata->element_label ?? null,
                    'element_enum' => $metadata->element_enum ?? null,
                    'element_note' => $metadata->element_note ?? null,
                ];
            });

        return Inertia::render('Respondents/Record', [
            'record' => $records,
            'project' => $project
        ]);
    }
}
