<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    private function userCanViewProject($project)
    {
        $user = auth()->user();
        
        // Admin can view any project
        if ($user->isAdmin()) {
            return true;
        }
        
        // Owner can view their project
        if ($project->owner_id == $user->id) {
            return true;
        }
        
        // Project manager (by email) can view
        if (!empty($project->project_manager_email) && $project->project_manager_email === $user->email) {
            return true;
        }
        
        return false;
    }

    private function userCanEditProject($project)
    {
        $user = auth()->user();
        
        // Admin can edit any project
        if ($user->isAdmin()) {
            return true;
        }
        
        // Owner can edit their project
        if ($project->owner_id == $user->id) {
            return true;
        }
        
        // Project manager (by email) can edit
        if (!empty($project->project_manager_email) && $project->project_manager_email === $user->email) {
            return true;
        }
        
        return false;
    }

    public function index()
    {
        // Filter projects based on user role
        if (auth()->user()->isAdmin()) {
            // Admins see all projects
            $projects = Project::orderBy('created_at', 'desc')->get();
        } else {
            // Regular users only see projects they own or manage
            $projects = Project::where(function($query) {
                $query->where('owner_id', auth()->id())
                      ->orWhere('project_manager_email', auth()->user()->email);
            })->orderBy('created_at', 'desc')->get();
        }

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
            'project_manager_email' => 'nullable|email',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'wall_height_cm' => $validated['wall_height_cm'],
            'wall_width_cm' => $validated['wall_width_cm'],
            'location_url' => $validated['location_url'],
            'project_manager_email' => $validated['project_manager_email'],
            'permalink' => Str::random(10),
            'owner_id' => auth()->id(),
            'owner_name' => auth()->user()->name,
        ]);

        return redirect()->route('projects.index')->with('success', 
            'Project "' . $validated['name'] . '" has been created successfully!');
    }

    public function show(Project $project)
    {
        // Check permissions
        if (!$this->userCanViewProject($project)) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to view this project.');
        }

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        // Check permissions
        if (!$this->userCanEditProject($project)) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to edit this project.');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        // Check permissions
        if (!$this->userCanEditProject($project)) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to edit this project.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'wall_height_cm' => 'required|numeric|min:1',
            'wall_width_cm' => 'required|numeric|min:1',
            'location_url' => 'nullable|url',
            'project_manager_email' => 'nullable|email',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 
            'Project "' . $validated['name'] . '" has been updated successfully!');
    }

    public function destroy(Project $project)
    {
        // Check permissions (only owner, project manager, or admin can delete)
        if (!$this->userCanEditProject($project)) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to delete this project.');
        }

        $projectName = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', 
            'Project "' . $projectName . '" has been deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id'
        ]);

        $projectIds = $request->project_ids;
        $projects = Project::whereIn('id', $projectIds)->get();
        
        // Check permissions for each project
        $deletedCount = 0;
        foreach ($projects as $project) {
            if ($this->userCanEditProject($project)) {
                $project->delete();
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            return redirect()->route('projects.index')->with('error', 
                'You do not have permission to delete any of the selected projects.');
        }

        return redirect()->route('projects.index')->with('success', 
            "Successfully deleted {$deletedCount} project(s).");
    }

    public function showByPermalink($permalink)
    {
        $project = Project::where('permalink', $permalink)->firstOrFail();
        
        // Check if user can view this project
        if (!$this->userCanViewProject($project)) {
            abort(403, 'You do not have permission to view this project. Only the project owner, project manager, and admins can access this project.');
        }

        return view('projects.show', compact('project'));
    }
}