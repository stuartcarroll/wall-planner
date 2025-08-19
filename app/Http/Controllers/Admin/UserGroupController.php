<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $userGroups = UserGroup::withCount('users')->orderBy('name')->get();
        return view('admin.user-groups.index', compact('userGroups'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        return view('admin.user-groups.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string|max:500',
        ]);

        UserGroup::create($validated);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group created successfully!');
    }

    public function show(UserGroup $userGroup)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $userGroup->load('users');
        return view('admin.user-groups.show', compact('userGroup'));
    }

    public function edit(UserGroup $userGroup)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        return view('admin.user-groups.edit', compact('userGroup'));
    }

    public function update(Request $request, UserGroup $userGroup)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name,' . $userGroup->id,
            'description' => 'nullable|string|max:500',
        ]);

        $userGroup->update($validated);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group updated successfully!');
    }

    public function destroy(UserGroup $userGroup)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $groupName = $userGroup->name;
        $userGroup->delete();

        return redirect()->route('admin.user-groups.index')->with('success', "User group '$groupName' has been deleted successfully!");
    }
}