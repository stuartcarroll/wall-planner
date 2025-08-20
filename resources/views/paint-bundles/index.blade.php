<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Paint Bundles') }}
            </h2>
            <a href="{{ route('paint-bundles.create') }}" 
               class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                Create New Bundle
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
                <div class="p-6">
                    
                    <!-- Info Banner -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    Paint Bundle System
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Create paint bundles for your projects to calculate costs, track quantities, and organize paint purchases. You can add paints from the catalog and get instant cost calculations.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($bundles->count() > 0)
                        <!-- Bundles Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($bundles as $bundle)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $bundle->name }}</h3>
                                        @if($bundle->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($bundle->description, 100) }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-green-600">
                                            £{{ number_format($bundle->total_cost, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">Total Cost</div>
                                    </div>
                                </div>

                                <!-- Project Info -->
                                <div class="mb-4">
                                    @php
                                        $project = $projects->get($bundle->project_id);
                                    @endphp
                                    @if($project)
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">Project:</span> {{ $project->name }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Bundle Stats -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="text-center">
                                        <div class="text-lg font-semibold text-blue-600">{{ $bundle->items_count ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">Paint Items</div>
                                    </div>
                                    <div class="text-center">
                                        @php
                                            $totalVolume = collect($bundle->items ?? [])->sum('volume_ml');
                                        @endphp
                                        <div class="text-lg font-semibold text-purple-600">
                                            @if($totalVolume >= 1000)
                                                {{ number_format($totalVolume / 1000, 1) }}L
                                            @else
                                                {{ $totalVolume }}ml
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">Total Volume</div>
                                    </div>
                                </div>

                                <!-- Color Swatches Preview -->
                                @if(isset($bundle->items) && count($bundle->items) > 0)
                                <div class="mb-4">
                                    <div class="flex space-x-1 overflow-hidden">
                                        @foreach(array_slice($bundle->items, 0, 6) as $item)
                                        <div class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0" 
                                             style="background-color: {{ $item['paint_hex_color'] }}"></div>
                                        @endforeach
                                        @if(count($bundle->items) > 6)
                                        <div class="text-xs text-gray-500 ml-2">+{{ count($bundle->items) - 6 }} more</div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Created Info -->
                                <div class="text-xs text-gray-500 mb-4">
                                    Created by {{ $bundle->created_by_name ?? 'Unknown' }} 
                                    @if(isset($bundle->created_at))
                                        on {{ date('M j, Y', strtotime($bundle->created_at)) }}
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('paint-bundles.show', $bundle->id) }}" 
                                       class="flex-1 text-center bg-blue-500 text-white py-2 px-4 rounded text-sm hover:bg-blue-600 transition-colors">
                                        View Details
                                    </a>
                                    @if(auth()->user()->isAdmin() || $bundle->created_by == auth()->id())
                                    <a href="{{ route('paint-bundles.edit', $bundle->id) }}" 
                                       class="bg-gray-500 text-white py-2 px-4 rounded text-sm hover:bg-gray-600 transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('paint-bundles.destroy', $bundle->id) }}" 
                                          class="inline" onsubmit="return confirm('Are you sure you want to delete this bundle?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-500 text-white py-2 px-4 rounded text-sm hover:bg-red-600 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Summary Stats -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6">
                            <h4 class="font-medium text-gray-900 mb-4">Bundle Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $bundles->count() }}</div>
                                    <div class="text-sm text-gray-600">Total Bundles</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">£{{ number_format($bundles->sum('total_cost'), 2) }}</div>
                                    <div class="text-sm text-gray-600">Total Value</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $bundles->sum('items_count') }}</div>
                                    <div class="text-sm text-gray-600">Total Paint Items</div>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No paint bundles yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Create your first paint bundle to organize paints for your projects.</p>
                            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('paint-bundles.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Create First Bundle
                                </a>
                                <a href="{{ route('paints.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Browse Paint Catalog
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>