<?php

namespace App\Http\Controllers;

use App\Models\Project;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;

class ProjectsController extends Controller
{
    /**
     * display a listing of all projects
     *
     * @return Response
     */

    public function index(): Response
    {
        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters(['status', 'search'])
            ->allowedSorts(['app_title', 'creation_time'])
            ->filter(Request::only('search', 'trashed'))
            ->paginate(10)
            ->withQueryString()
            ->through(fn($project) => [
                'id' => $project->project_id,
                'app_title' => $project->app_title,
                'creation_time' => $project->creation_time,
                'status' => $project->status,
            ]);

        return Inertia::render('Projects/Index', [
            'filters' => Request::all('search', 'sort'),
            'projects' => $projects
        ]);
    }

    /**
     * Display specified project
     * 
     * @param Project $project
     * @return \Inertia\Response
     */

    public function show(Project $project): Response
    {


        return Inertia::render('Projects/Show', [
            'projectInfo' => $project,
            'projectData' => $project->projectDataCounts,
            'projectEventArms' => $project
                ->project_events_arms()
                ->with('project_events_metadata')
                ->get(),
            'projectForms' => $project
                ->project_metadata()
                ->distinct()
                ->get('form_name', 'form_names')

        ]);
    }
}
