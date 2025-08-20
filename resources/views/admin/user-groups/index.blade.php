<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Groups') }}
            </h2>
            <a href="{{ route('admin.user-groups.create') }}" 
               class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Create Group
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                            <div class="text-green-800">{{ session('success') }}</div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                            <div class="text-red-800">{{ session('error') }}</div>
                        </div>
                    @endif

                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    User Groups System
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>User groups allow you to organize users by role and permissions. For example: "Artists" (can manage paint catalog), "Project Managers" (can create projects), "Painters" (can add progress photos).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($userGroups->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($userGroups as $group)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-semibold text-lg">{{ $group->name }}</h4>
                                <p class="text-gray-600 text-sm mt-1">{{ $group->description ?: 'No description provided' }}</p>
                                <div class="mt-3 flex justify-between items-center">
                                    <span class="text-xs text-gray-500">{{ $group->users_count }} member{{ $group->users_count !== 1 ? 's' : '' }}</span>
                                    <div class="space-x-2">
                                        <a href="{{ route('admin.user-groups.show', $group) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                                            View
                                        </a>
                                        <a href="{{ route('admin.user-groups.manage-users', $group) }}" class="text-green-500 hover:text-green-700 text-sm">
                                            Users
                                        </a>
                                        <a href="{{ route('admin.user-groups.edit', $group) }}" class="text-indigo-500 hover:text-indigo-700 text-sm">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.user-groups.destroy', $group) }}" 
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this group?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No user groups yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Create groups to organize users by their roles and permissions.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.user-groups.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Create First Group
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Example Groups Preview -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Example User Groups:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="bg-white p-3 rounded border">
                                <h5 class="font-medium text-blue-600">Artists</h5>
                                <p class="text-gray-600 text-xs mt-1">Can manage paint catalog, add colors, create paint bundles</p>
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <h5 class="font-medium text-green-600">Project Managers</h5>
                                <p class="text-gray-600 text-xs mt-1">Can create projects, manage teams, oversee budgets</p>
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <h5 class="font-medium text-purple-600">Painters</h5>
                                <p class="text-gray-600 text-xs mt-1">Can view projects, add progress photos, update status</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>