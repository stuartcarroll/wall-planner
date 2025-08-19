<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" 
               class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                New Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($projects->count() > 0)
                        <!-- Project Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-semibold text-lg text-gray-900">{{ $project->name }}</h3>
                                    @if(auth()->user()->isAdmin())
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Admin View
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $project->description }}</p>
                                
                                <div class="space-y-2 text-sm text-gray-500 mb-4">
                                    <div><strong>Location:</strong> {{ $project->location }}</div>
                                    <div><strong>Dimensions:</strong> {{ $project->wall_width_cm }}cm × {{ $project->wall_height_cm }}cm</div>
                                    <div><strong>Owner:</strong> {{ $project->owner_name }}</div>
                                    <div><strong>Created:</strong> {{ date('M j, Y', strtotime($project->created_at)) }}</div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('projects.show', $project->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        View
                                    </a>
                                    
                                    @if(auth()->user()->isAdmin() || $project->owner_id == auth()->id())
                                        <a href="{{ route('projects.edit', $project->id) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('projects.destroy', $project->id) }}" 
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No projects yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(auth()->user()->isAdmin())
                                    No projects have been created yet.
                                @else
                                    You haven't created any projects yet.
                                @endif
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('projects.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    Create Your First Project
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>