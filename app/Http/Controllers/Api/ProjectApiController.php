<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            // Admins can see all projects
            $projects = Project::with('user')->orderBy('created_at', 'desc')->get();
        } else {
            // Regular users can only see their own projects or projects they manage
            $projects = Project::with('user')
                ->where(function($query) use ($user) {
                    $query->where('owner_id', $user->id)
                          ->orWhere('manager_email', $user->email);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'data' => $projects,
            'can_create' => true,
            'is_admin' => $user->isAdmin(),
        ]);
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can view this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $project->load(['user']),
            'can_edit' => $user->isAdmin() || $project->owner_id === $user->id || $project->manager_email === $user->email,
            'can_delete' => $user->isAdmin() || $project->owner_id === $user->id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'wall_height_cm' => 'nullable|numeric|min:0',
            'wall_width_cm' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,completed,on_hold',
            'manager_email' => 'nullable|email|exists:users,email',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'location_url' => $request->location_url,
            'wall_height_cm' => $request->wall_height_cm,
            'wall_width_cm' => $request->wall_width_cm,
            'status' => $request->status ?? 'active',
            'manager_email' => $request->manager_email,
            'owner_id' => $request->user()->id,
            'permalink' => Str::random(8),
        ]);

        return response()->json([
            'data' => $project->load('user'),
            'message' => 'Project created successfully'
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can edit this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'wall_height_cm' => 'nullable|numeric|min:0',
            'wall_width_cm' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,completed,on_hold',
            'manager_email' => 'nullable|email|exists:users,email',
        ]);

        $project->update($request->only([
            'name', 'description', 'location', 'location_url', 'wall_height_cm', 'wall_width_cm', 'status', 'manager_email'
        ]));

        return response()->json([
            'data' => $project->load('user'),
            'message' => 'Project updated successfully'
        ]);
    }

    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can delete this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    public function addMember(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can manage this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $memberUser = User::where('email', $request->email)->first();
        
        // Add logic to handle project members if you have a members table
        // For now, we'll just update the manager_email field
        $project->update(['manager_email' => $request->email]);

        return response()->json([
            'message' => 'Member added successfully',
            'data' => $project->load('user')
        ]);
    }

    public function removeMember(Request $request, Project $project, User $user)
    {
        $currentUser = $request->user();
        
        // Check if user can manage this project
        if (!$currentUser->isAdmin() && $project->owner_id !== $currentUser->id && $project->manager_email !== $currentUser->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Remove member logic (for now, just clear manager_email if it matches)
        if ($project->manager_email === $user->email) {
            $project->update(['manager_email' => null]);
        }

        return response()->json([
            'message' => 'Member removed successfully',
            'data' => $project->load('user')
        ]);
    }

    public function getUsers(Request $request)
    {
        $user = $request->user();
        
        // Only allow authenticated users to see user list for project management
        $users = User::select('id', 'name', 'email')
                    ->orderBy('name')
                    ->get();

        return response()->json([
            'data' => $users
        ]);
    }
}