<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 3000);

use App\Models\ProjectMetadata;
use App\Models\ProjectData;
use App\Models\Project;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectDataReportingController extends Controller
{
    //
    public function read(string $id): Response
    {

        $project_data = ProjectData::query();
        // $project = Project::findOrFail($id);
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($id);

        return Inertia::render(
            'Reports/Index',
            [
                'appTitle' => $project->app_title,
                'metadata' => ProjectMetadata::where('project_id', $id)
                    ->orderBy('field_order', 'asc')
                    ->get(),
                'actualData' =>  $project_data->where('project_id', $id)
                    ->select('field_name', DB::raw('count(field_name) as value'))
                    ->groupBy('field_name')
                    ->get(),
                'projectForms' => $project
                    ->project_metadata()
                    ->distinct()
                    ->get('form_name')

            ]

        );
    }



    /**
     *  fetch respondent responses
     */

    public function getRespondentResponses(string $projectId, string $record)
    {

        $respondent_data = ProjectData::query()->where('project_id', $projectId)
            ->where('record', $record)
            ->with('project_event_metadata')
            ->orderBy('event_id', 'asc')
            ->get()->map(fn($project_data) => [
                'project_id' => $project_data->project_id,
                'event_id' => $project_data->event_id,
                'field_name' => $project_data->field_name,
                'value' => $project_data->value,
                'description' => $project_data->project_event_metadata->descrip,
                'custom_event_label' => $project_data->project_event_metadata->custom_event_label
            ])
            ->groupBy('description');

        return Inertia::render(
            'Reports/Record',
            [
                'respondentData' => $respondent_data,
                'metadata' => ProjectMetadata::where('project_id', $projectId)
                    ->orderBy('field_order', 'asc')
                    ->get()
                    ->map(fn($project_metadata) => [
                        'project_id' => $project_metadata->project_id,
                        'field_name' => $project_metadata->field_name,
                        'form_name' => $project_metadata->form_name,
                        'form_menu_description' => $project_metadata->form_menu_description,
                        'element_label' => $project_metadata->element_label,
                        'element_enum' => $project_metadata->element_enum,
                        'element_note' => $project_metadata->element_note,
                    ]),
                'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
                'record' => $record
            ]
        );
    }

    /**
     * create 
     */

    public function filterDashboard(string $projectId)
    {

        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
        $project_meta = ProjectMetadata::query()
            ->where('project_id', '=', $projectId)
            ->select(
                'field_name',
                'element_enum',
                'element_type',
                'element_label',
                'element_validation_type',
                'element_validation_min',
                'element_validation_max'
            )
            ->get();

        //dd($project);

        return Inertia::render('Projects/Dashboard', [
            'project' => $project,
            'metadata' => $project_meta
        ]);
    }

    /**
     * run data query
     */

    public function dataQuery_(string $projectId)
    {


        $offset = 0;
        $limit = 100;


        $chunkSize = 1000; // Adjust chunk size based on your server's capacity
        $offset = 0;
        $respondents = [];
        $respondentData = [];

        while (true) {
            $chunk = DB::table('redcap_data')
                ->where('project_id', $projectId)
                ->where('field_name', 'ncd_weight')
                ->where('value', '>', 50)
                ->skip($offset)
                ->take($chunkSize)
                ->get();

            if ($chunk->isEmpty()) {
                break;
            }

            $respondents = array_merge($respondents, $chunk->toArray());
            $offset += $chunkSize;
        }



        $newArray = ProjectData::query()->where('project_id', $projectId)
            ->where('event_id', '476')
            ->where('record', 'CHK0001')
            // ->with('project_event_metadata')
            //->orderBy('event_id', 'asc')
            ->get()
            ->map(fn($project_data) => [
                //'project_id' => $project_data->project_id,
                //'event_id' => $project_data->event_id,
                'field_name' => $project_data->field_name,
                'value' => $project_data->value,
                //'description' => $project_data->project_event_metadata->descrip,
                //'custom_event_label' => $project_data->project_event_metadata->custom_event_label
            ])->lazy()->transpose();



        return Inertia::render('Data/Show', [
            'data' => $respondents,
            'newArray' =>  $newArray
        ]);
    }

    //
    public function dataQuery(string $projectId)
    {


        // Step 1: Set the SQL variable
        DB::statement("SET @sql = NULL");

        // Step 2: Generate the dynamic SQL with conditions
        DB::statement("
            SELECT 
                GROUP_CONCAT(DISTINCT 
                    CONCAT(
                        'MAX(CASE WHEN field_name = ''', field_name, ''' THEN value END) AS `', field_name, '`'
                    )
                ) INTO @sql
            FROM 
                redcap_data
            WHERE 
                project_id = $projectId
        ");

        // Step 3: Concatenate the final SQL query
        DB::statement("
            SET @sql = CONCAT('SELECT record, ', @sql, ' FROM redcap_data WHERE project_id = $projectId AND record IN (SELECT record FROM redcap_data WHERE field_name = ''ncd_weight'' AND value > 50) GROUP BY record')
        ");

        // Step 4: Prepare and execute the statement
        DB::statement("PREPARE stmt FROM @sql");
        $results = DB::select("EXECUTE stmt");
        DB::statement("DEALLOCATE PREPARE stmt");


        // Step 5: Return the results to the Inertia view
        return Inertia::render('Data/Show', [
            'data' => $results,
        ]);
    }


}
