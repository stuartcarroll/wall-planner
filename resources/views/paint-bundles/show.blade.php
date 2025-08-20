<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Paint Bundle: {{ $bundle->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('paint-bundles.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    Back to Bundles
                </a>
                @if(auth()->user()->isAdmin() || $bundle->created_by == auth()->id())
                <a href="{{ route('paint-bundles.edit', $bundle->id) }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    Edit Bundle
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
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

            <!-- Bundle Header Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Bundle Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Bundle Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Bundle Name</dt>
                                    <dd class="text-lg text-gray-900">{{ $bundle->name }}</dd>
                                </div>
                                
                                @if($bundle->description)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="text-gray-900">{{ $bundle->description }}</dd>
                                </div>
                                @endif
                                
                                @if($project)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Project</dt>
                                    <dd class="text-gray-900">{{ $project->name }}</dd>
                                </div>
                                @endif
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                    <dd class="text-gray-900">{{ $bundle->created_by_name ?? 'Unknown' }}</dd>
                                </div>
                                
                                @if(isset($bundle->created_at))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created On</dt>
                                    <dd class="text-gray-900">{{ date('M j, Y \a\t g:i A', strtotime($bundle->created_at)) }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Cost Summary -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cost Summary</h3>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">£{{ number_format($bundle->total_cost, 2) }}</div>
                                        <div class="text-sm text-gray-600">Total Cost</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">{{ $bundle->items_count ?? 0 }}</div>
                                        <div class="text-sm text-gray-600">Paint Items</div>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    @php
                                        $totalVolume = collect($bundle->items ?? [])->sum('volume_ml');
                                    @endphp
                                    <div class="text-2xl font-bold text-purple-600">
                                        @if($totalVolume >= 1000)
                                            {{ number_format($totalVolume / 1000, 1) }}L
                                        @else
                                            {{ $totalVolume }}ml
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600">Total Volume</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paint Items -->
            @if(isset($bundle->items) && count($bundle->items) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Paint Items ({{ count($bundle->items) }})</h3>
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Color
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Paint Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Volume
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($bundle->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <!-- Color Swatch -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-sm" 
                                             style="background-color: {{ $item['paint_hex_color'] }}"></div>
                                    </td>
                                    
                                    <!-- Paint Details -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['paint_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['paint_maker'] }}</div>
                                        <div class="text-xs font-mono text-gray-400">{{ $item['paint_hex_color'] }}</div>
                                    </td>
                                    
                                    <!-- Quantity -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-semibold text-blue-600">{{ $item['quantity'] }}</span>
                                    </td>
                                    
                                    <!-- Unit Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">£{{ number_format($item['unit_price'], 2) }}</span>
                                    </td>
                                    
                                    <!-- Volume -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">
                                            @if($item['volume_ml'] >= 1000)
                                                {{ number_format($item['volume_ml'] / 1000, 1) }}L
                                            @else
                                                {{ $item['volume_ml'] }}ml
                                            @endif
                                        </span>
                                    </td>
                                    
                                    <!-- Subtotal -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-bold text-green-600">£{{ number_format($item['subtotal'], 2) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        @foreach($bundle->items as $item)
                        <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                            <div class="flex items-start space-x-4">
                                <!-- Color Swatch -->
                                <div class="w-16 h-16 rounded-lg border border-gray-300 flex-shrink-0" 
                                     style="background-color: {{ $item['paint_hex_color'] }}"></div>
                                
                                <!-- Paint Details -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-semibold text-gray-900 truncate">{{ $item['paint_name'] }}</h4>
                                    <p class="text-gray-600">{{ $item['paint_maker'] }}</p>
                                    <p class="text-xs font-mono text-gray-400 mb-2">{{ $item['paint_hex_color'] }}</p>
                                    
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Quantity:</span>
                                            <span class="font-semibold text-blue-600">{{ $item['quantity'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Unit Price:</span>
                                            <span class="font-medium">£{{ number_format($item['unit_price'], 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Volume:</span>
                                            <span class="font-medium">
                                                @if($item['volume_ml'] >= 1000)
                                                    {{ number_format($item['volume_ml'] / 1000, 1) }}L
                                                @else
                                                    {{ $item['volume_ml'] }}ml
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Subtotal:</span>
                                            <span class="font-bold text-green-600">£{{ number_format($item['subtotal'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No paint items</h3>
                    <p class="mt-1 text-sm text-gray-500">This bundle doesn't contain any paint items yet.</p>
                    @if(auth()->user()->isAdmin() || $bundle->created_by == auth()->id())
                    <div class="mt-6">
                        <a href="{{ route('paint-bundles.edit', $bundle->id) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Add Paint Items
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if(auth()->user()->isAdmin() || $bundle->created_by == auth()->id())
            <div class="mt-6 flex justify-center gap-4">
                <a href="{{ route('paint-bundles.edit', $bundle->id) }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    Edit Bundle
                </a>
                <form method="POST" action="{{ route('paint-bundles.destroy', $bundle->id) }}" 
                      class="inline" onsubmit="return confirm('Are you sure you want to delete this bundle? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 font-medium">
                        Delete Bundle
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>