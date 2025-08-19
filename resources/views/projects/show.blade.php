<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            @if(auth()->user()->isAdmin() || $project->owner_id == auth()->id())
                <div class="flex gap-2">
                    <a href="{{ route('projects.edit', $project->id) }}" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Edit Project
                    </a>
                    <form method="POST" action="{{ route('projects.destroy', $project->id) }}" 
                          class="inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                            Delete Project
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Project Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Project Name</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $project->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="text-gray-900">{{ $project->description }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                                    <dd class="text-gray-900">{{ $project->location }}</dd>
                                </div>
                                @if($project->location_url)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Map Link</dt>
                                    <dd><a href="{{ $project->location_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">View on Google Maps</a></dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Owner</dt>
                                    <dd class="text-gray-900">{{ $project->owner_name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Wall Specifications -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Wall Specifications</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $project->wall_width_cm }}cm</div>
                                        <div class="text-sm text-gray-600">Width</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ $project->wall_height_cm }}cm</div>
                                        <div class="text-sm text-gray-600">Height</div>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <div class="text-lg font-semibold text-purple-600">
                                        {{ number_format(($project->wall_width_cm * $project->wall_height_cm) / 10000, 2) }} m²
                                    </div>
                                    <div class="text-sm text-gray-600">Total Area</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Timeline -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Timeline</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium">{{ date('F j, Y g:i A', strtotime($project->created_at)) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium">{{ date('F j, Y g:i A', strtotime($project->updated_at)) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Permalink:</span>
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $project->permalink }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('projects.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            ← Back to Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>