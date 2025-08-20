<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PaintBundle;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's projects or all projects if admin
        if ($user->isAdmin()) {
            $userProjects = Project::query();
            $recentProjects = Project::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        } else {
            $userProjects = Project::where(function($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhere('manager_email', $user->email);
            });
            $recentProjects = Project::with('user')
                ->where(function($query) use ($user) {
                    $query->where('owner_id', $user->id)
                          ->orWhere('manager_email', $user->email);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Calculate stats
        $totalProjects = $userProjects->count();
        $activeProjects = $userProjects->where('status', 'active')->count();
        $completedProjects = $userProjects->where('status', 'completed')->count();
        
        // Count paint bundles
        if ($user->isAdmin()) {
            $totalPaintBundles = PaintBundle::count();
        } else {
            $totalPaintBundles = PaintBundle::where('created_by', $user->id)->count();
        }

        return response()->json([
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'total_paint_bundles' => $totalPaintBundles,
            'recent_projects' => $recentProjects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'location' => $project->location,
                    'created_at' => $project->created_at,
                    'user' => [
                        'name' => $project->user->name ?? 'Unknown',
                        'email' => $project->user->email ?? 'unknown@example.com',
                    ]
                ];
            }),
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->isAdmin(),
                'role' => $user->role,
            ]
        ]);
    }
}