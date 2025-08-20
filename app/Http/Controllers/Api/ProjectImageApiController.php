<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectImageApiController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can view this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $images = $project->images()
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $images
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user can edit this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'type' => 'required|in:photo,sketch,inspiration',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('image');
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store in public/storage/project_images
        $path = $file->storeAs('project_images', $filename, 'public');
        
        // Get image dimensions
        $imageInfo = getimagesize($file->getRealPath());
        $width = $imageInfo ? $imageInfo[0] : null;
        $height = $imageInfo ? $imageInfo[1] : null;

        $projectImage = ProjectImage::create([
            'project_id' => $project->id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'type' => $request->type,
            'description' => $request->description,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);

        return response()->json([
            'data' => $projectImage,
            'message' => 'Image uploaded successfully'
        ], 201);
    }

    public function update(Request $request, Project $project, ProjectImage $projectImage)
    {
        $user = $request->user();
        
        // Check if user can edit this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Make sure the image belongs to this project
        if ($projectImage->project_id !== $project->id) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $request->validate([
            'type' => 'sometimes|in:photo,sketch,inspiration',
            'description' => 'nullable|string|max:500',
        ]);

        $projectImage->update($request->only(['type', 'description']));

        return response()->json([
            'data' => $projectImage,
            'message' => 'Image updated successfully'
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectImage $projectImage)
    {
        $user = $request->user();
        
        // Check if user can edit this project
        if (!$user->isAdmin() && $project->owner_id !== $user->id && $project->manager_email !== $user->email) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Make sure the image belongs to this project
        if ($projectImage->project_id !== $project->id) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        // Delete the file from storage
        Storage::disk('public')->delete('project_images/' . $projectImage->filename);

        // Delete the database record
        $projectImage->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }
}