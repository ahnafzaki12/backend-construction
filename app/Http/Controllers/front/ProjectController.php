<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'DESC')->get();
        return $projects;
    }

    public function latestProjects(Request $request){
        $projects = Project::take($request->get('limit'))->orderBy('created_at', 'DESC')->get();
        return $projects;
    }
}
