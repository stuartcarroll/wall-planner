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

                    <!-- Paint Bundles & Cost Summary -->
                    @php
                        $projectBundles = collect(Session::get('paint_bundles', []))->filter(function($bundle) use ($project) {
                            return $bundle->project_id == $project->id;
                        });
                        $totalBundleCost = $projectBundles->sum('total_cost');
                        $totalPaintItems = $projectBundles->sum('items_count');
                    @endphp

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Paint Bundles & Costs</h3>
                            <a href="{{ route('paint-bundles.create') }}?project_id={{ $project->id }}" 
                               class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 transition-colors text-sm">
                                🎨 Add Paint Bundle
                            </a>
                        </div>

                        @if($projectBundles->count() > 0)
                            <!-- Cost Overview -->
                            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 mb-6">
                                <h4 class="font-medium text-gray-900 mb-4">Project Cost Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">£{{ number_format($totalBundleCost, 2) }}</div>
                                        <div class="text-sm text-gray-600">Total Paint Cost</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">{{ $projectBundles->count() }}</div>
                                        <div class="text-sm text-gray-600">Paint Bundles</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-purple-600">{{ $totalPaintItems }}</div>
                                        <div class="text-sm text-gray-600">Paint Items</div>
                                    </div>
                                </div>
                                
                                <!-- Cost per square meter -->
                                @php
                                    $totalAreaM2 = ($project->wall_width_cm * $project->wall_height_cm) / 10000;
                                    $costPerM2 = $totalAreaM2 > 0 ? $totalBundleCost / $totalAreaM2 : 0;
                                @endphp
                                <div class="mt-4 text-center border-t pt-4">
                                    <div class="text-xl font-semibold text-orange-600">£{{ number_format($costPerM2, 2) }}/m²</div>
                                    <div class="text-sm text-gray-600">Cost per Square Meter</div>
                                </div>
                            </div>

                            <!-- Bundle List -->
                            <div class="space-y-4">
                                @foreach($projectBundles as $bundle)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h5 class="font-semibold text-gray-900">{{ $bundle->name }}</h5>
                                                <span class="text-2xl font-bold text-green-600">£{{ number_format($bundle->total_cost, 2) }}</span>
                                            </div>
                                            
                                            @if($bundle->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($bundle->description, 100) }}</p>
                                            @endif
                                            
                                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                                <span>{{ $bundle->items_count ?? 0 }} paint items</span>
                                                <span>•</span>
                                                <span>Created by {{ $bundle->created_by_name ?? 'Unknown' }}</span>
                                                @if(isset($bundle->created_at))
                                                <span>•</span>
                                                <span>{{ date('M j, Y', strtotime($bundle->created_at)) }}</span>
                                                @endif
                                            </div>

                                            <!-- Color Swatches Preview -->
                                            @if(isset($bundle->items) && count($bundle->items) > 0)
                                            <div class="mt-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex space-x-1">
                                                        @foreach(array_slice($bundle->items, 0, 8) as $item)
                                                        <div class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0" 
                                                             style="background-color: {{ $item['paint_hex_color'] }}"
                                                             title="{{ $item['paint_name'] }} ({{ $item['quantity'] }})"></div>
                                                        @endforeach
                                                        @if(count($bundle->items) > 8)
                                                        <div class="text-xs text-gray-500 ml-2">+{{ count($bundle->items) - 8 }} more</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex gap-2 ml-4">
                                            <a href="{{ route('paint-bundles.show', $bundle->id) }}" 
                                               class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition-colors">
                                                View
                                            </a>
                                            @if(auth()->user()->isAdmin() || $bundle->created_by == auth()->id())
                                            <a href="{{ route('paint-bundles.edit', $bundle->id) }}" 
                                               class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600 transition-colors">
                                                Edit
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        @else
                            <!-- Empty State -->
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <h4 class="mt-2 text-sm font-medium text-gray-900">No paint bundles yet</h4>
                                <p class="mt-1 text-sm text-gray-500">Create paint bundles to track costs and organize paints for this project.</p>
                                <div class="mt-4">
                                    <a href="{{ route('paint-bundles.create') }}?project_id={{ $project->id }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                        Create First Bundle
                                    </a>
                                </div>
                            </div>
                        @endif
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
                            <div class="text-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Permalink:</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.permalink', $project->permalink) }}" 
                                           class="font-mono text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded hover:bg-blue-200 transition-colors"
                                           target="_blank" title="Open shareable link">
                                            {{ url('/p/' . $project->permalink) }}
                                        </a>
                                        <button onclick="copyPermalink('{{ url('/p/' . $project->permalink) }}')" 
                                                class="text-gray-500 hover:text-gray-700 transition-colors" 
                                                title="Copy link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                    🔒 <strong>Access Restricted:</strong> This link requires login and can only be accessed by the project owner, project manager{{ $project->project_manager_email ? ' (' . $project->project_manager_email . ')' : '' }}, or admins.
                                </div>
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

    <script>
        function copyPermalink(url) {
            navigator.clipboard.writeText(url).then(function() {
                // Show success feedback
                const button = event.target.closest('button');
                const originalTitle = button.title;
                button.title = 'Copied!';
                button.classList.add('text-green-600');
                
                setTimeout(() => {
                    button.title = originalTitle;
                    button.classList.remove('text-green-600');
                }, 2000);
            }).catch(function() {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                alert('Link copied to clipboard!');
            });
        }
    </script>
</x-app-layout>