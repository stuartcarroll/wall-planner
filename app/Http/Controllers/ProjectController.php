<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    private function getProjects()
    {
        return collect(Session::get('projects', []));
    }

    private function saveProjects($projects)
    {
        Session::put('projects', $projects->toArray());
    }

    public function index()
    {
        $projects = $this->getProjects();
        
        // Filter projects based on user role
        if (!auth()->user()->isAdmin()) {
            // Regular users only see their own projects
            $projects = $projects->where('owner_id', auth()->id());
        }
        // Admins see all projects

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'wall_height_cm' => 'required|numeric|min:1',
            'wall_width_cm' => 'required|numeric|min:1',
            'location_url' => 'nullable|url',
        ]);

        $projects = $this->getProjects();
        $nextId = $projects->keys()->max() + 1;
        
        $newProject = (object)[
            'id' => $nextId,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'wall_height_cm' => $validated['wall_height_cm'],
            'wall_width_cm' => $validated['wall_width_cm'],
            'location_url' => $validated['location_url'],
            'permalink' => Str::random(10),
            'owner_id' => auth()->id(),
            'owner_name' => auth()->user()->name,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $projects->put($nextId, $newProject);
        $this->saveProjects($projects);

        return redirect()->route('projects.index')->with('success', 
            'Project "' . $validated['name'] . '" has been created successfully!');
    }

    public function show(string $id)
    {
        $projects = $this->getProjects();
        $project = $projects->get($id);
        
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Check permissions
        if (!auth()->user()->isAdmin() && $project->owner_id != auth()->id()) {
            return redirect()->route('projects.index')->with('error', 'You can only view your own projects.');
        }

        return view('projects.show', compact('project'));
    }

    public function edit(string $id)
    {
        $projects = $this->getProjects();
        $project = $projects->get($id);
        
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Check permissions
        if (!auth()->user()->isAdmin() && $project->owner_id != auth()->id()) {
            return redirect()->route('projects.index')->with('error', 'You can only edit your own projects.');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, string $id)
    {
        $projects = $this->getProjects();
        $project = $projects->get($id);
        
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Check permissions
        if (!auth()->user()->isAdmin() && $project->owner_id != auth()->id()) {
            return redirect()->route('projects.index')->with('error', 'You can only edit your own projects.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'wall_height_cm' => 'required|numeric|min:1',
            'wall_width_cm' => 'required|numeric|min:1',
            'location_url' => 'nullable|url',
        ]);

        // Update project
        foreach ($validated as $key => $value) {
            $project->$key = $value;
        }
        $project->updated_at = now()->format('Y-m-d H:i:s');

        $projects->put($id, $project);
        $this->saveProjects($projects);

        return redirect()->route('projects.show', $id)->with('success', 
            'Project "' . $validated['name'] . '" has been updated successfully!');
    }

    public function destroy(string $id)
    {
        $projects = $this->getProjects();
        $project = $projects->get($id);
        
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Check permissions (only owner or admin can delete)
        if (!auth()->user()->isAdmin() && $project->owner_id != auth()->id()) {
            return redirect()->route('projects.index')->with('error', 'You can only delete your own projects.');
        }

        $projectName = $project->name;
        $projects->forget($id);
        $this->saveProjects($projects);

        return redirect()->route('projects.index')->with('success', 
            'Project "' . $projectName . '" has been deleted successfully!');
    }
}