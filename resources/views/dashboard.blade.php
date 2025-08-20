{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">
                        Welcome back, {{ auth()->user()->name }}!
                    </h3>
                    <p class="text-gray-600">
                        Ready to plan your next wall project? Here's what you can do:
                    </p>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Browse Paint Catalog -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-8 0 4 4 0 018 0zM7 21h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Paint Catalog</h3>
                                <p class="text-sm text-gray-500">Browse available paints and colors</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('paints.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Browse Paints →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Manage Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Projects</h3>
                                <p class="text-sm text-gray-500">Create and manage wall projects</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('projects.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                View Projects →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Add Paint (for authorized users) -->
                @can('manage-paints')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Add Paint</h3>
                                <p class="text-sm text-gray-500">Add new paints to the catalog</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('paints.create') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Add Paint →
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                <!-- Admin Panel (for admins only) -->
                @if(auth()->user()->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Admin Panel</h3>
                                <p class="text-sm text-gray-500">Manage users and settings</p>
                            </div>
                        </div>
                        <div class="mt-4 space-x-4">
                            <a href="{{ route('admin.users.index') }}" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Manage Users →
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- User Info & Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Account</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Role:</span>
                                <span class="font-medium">
                                    @if(auth()->user()->isAdmin())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Administrator
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            User
                                        </span>
                                    @endif
                                </span>
                            </div>
                            @if(auth()->user()->userGroups->count() > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Groups:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(auth()->user()->userGroups as $group)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $group->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Member since:</span>
                                <span class="font-medium">{{ auth()->user()->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Paints:</span>
                                <span class="font-medium">
                                    @php
                                        try {
                                            $paintCount = \App\Models\Paint::count();
                                        } catch (\Exception $e) {
                                            $paintCount = 0;
                                        }
                                    @endphp
                                    {{ $paintCount }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Your Projects:</span>
                                <span class="font-medium">
                                    @php
                                        try {
                                            $userProjectCount = \App\Models\Project::where('owner_id', auth()->id())->count();
                                        } catch (\Exception $e) {
                                            $userProjectCount = 0;
                                        }
                                    @endphp
                                    {{ $userProjectCount }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Users:</span>
                                <span class="font-medium">{{ \App\Models\User::count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>