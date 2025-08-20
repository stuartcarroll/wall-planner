<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use App\Models\User;
use Illuminate\Http\Request;

class UserGroupApiController extends Controller
{
    public function index(Request $request)
    {
        $query = UserGroup::with('users');
        
        // Add search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }
        
        $userGroups = $query->orderBy('name')->get();
        
        return response()->json([
            'data' => $userGroups
        ]);
    }

    public function show(UserGroup $userGroup)
    {
        $userGroup->load('users');
        
        return response()->json([
            'data' => $userGroup
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_groups',
            'description' => 'nullable|string|max:1000',
        ]);

        $userGroup = UserGroup::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'data' => $userGroup,
            'message' => 'User group created successfully'
        ], 201);
    }

    public function update(Request $request, UserGroup $userGroup)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:user_groups,name,' . $userGroup->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $userGroup->update($request->only(['name', 'description']));

        return response()->json([
            'data' => $userGroup,
            'message' => 'User group updated successfully'
        ]);
    }

    public function destroy(UserGroup $userGroup)
    {
        // Remove all users from the group before deleting
        $userGroup->users()->detach();
        $userGroup->delete();

        return response()->json([
            'message' => 'User group deleted successfully'
        ]);
    }

    public function addUser(Request $request, UserGroup $userGroup, User $user)
    {
        if (!$userGroup->users->contains($user->id)) {
            $userGroup->users()->attach($user->id);
        }

        return response()->json([
            'message' => 'User added to group successfully'
        ]);
    }

    public function removeUser(UserGroup $userGroup, User $user)
    {
        $userGroup->users()->detach($user->id);

        return response()->json([
            'message' => 'User removed from group successfully'
        ]);
    }
}