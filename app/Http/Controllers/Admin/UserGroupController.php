<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use App\Models\User;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function index()
    {
        $userGroups = UserGroup::withCount('users')->orderBy('name')->get();
        return view('admin.user-groups.index', compact('userGroups'));
    }

    public function create()
    {
        return view('admin.user-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string|max:500',
        ]);

        UserGroup::create($validated);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group created successfully!');
    }

    public function show(UserGroup $userGroup)
    {
        $userGroup->load('users');
        return view('admin.user-groups.show', compact('userGroup'));
    }

    public function edit(UserGroup $userGroup)
    {
        return view('admin.user-groups.edit', compact('userGroup'));
    }

    public function update(Request $request, UserGroup $userGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name,' . $userGroup->id,
            'description' => 'nullable|string|max:500',
        ]);

        $userGroup->update($validated);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group updated successfully!');
    }

    public function destroy(UserGroup $userGroup)
    {
        $groupName = $userGroup->name;
        $userGroup->delete();

        return redirect()->route('admin.user-groups.index')->with('success', "User group '$groupName' has been deleted successfully!");
    }

    public function manageUsers(UserGroup $userGroup)
    {
        $users = User::orderBy('name')->get();
        $userGroup->load('users');
        
        return view('admin.user-groups.manage-users', compact('userGroup', 'users'));
    }

    public function addUser(Request $request, UserGroup $userGroup)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($userGroup->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', "User '{$user->name}' is already in this group.");
        }

        $userGroup->users()->attach($user->id);

        return back()->with('success', "User '{$user->name}' has been added to the group.");
    }

    public function removeUser(Request $request, UserGroup $userGroup)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $userGroup->users()->detach($user->id);

        return back()->with('success', "User '{$user->name}' has been removed from the group.");
    }
}