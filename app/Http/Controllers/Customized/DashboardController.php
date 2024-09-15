<?php

namespace App\Http\Controllers\Customized;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function read(string $id)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($id);

        $customProjectData = DB::table('project_customization')
                ->where('project_id', $id)->get();


// dd($customProjectData[0]->tag);

        return Inertia::render(
            'Customized/Index',
            [
                'project' => $project,
                'customProjectData' => $customProjectData
            ]

        );

    }
}
