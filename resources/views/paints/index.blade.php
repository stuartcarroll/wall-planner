<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Paint Catalog') }}
            </h2>
            @if(auth()->user()->isAdmin())
                <div class="flex gap-2">
                    <a href="{{ route('paints.create') }}" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        Add New Paint
                    </a>
                    <button class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors" disabled>
                        Bulk Import
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Info Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('info'))
                <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                    {{ session('info') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <form method="GET" class="flex gap-4">
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Search paints by name, maker, or code..." 
                                value="{{ request('search') }}"
                                class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <button type="submit" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('paints.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    @if($paints->count() > 0)
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Color
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product Details
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Specifications
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price & Volume
                                        </th>
                                        @if(auth()->user()->isAdmin())
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paints as $paint)
                                    <tr class="hover:bg-gray-50">
                                        <!-- Color Swatch -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-sm" 
                                                     style="background-color: {{ $paint->hex_color }}"></div>
                                                <div class="ml-3">
                                                    <div class="text-xs font-mono text-gray-500">{{ $paint->hex_color }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Product Details -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $paint->product_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $paint->maker }}</div>
                                            <div class="text-xs text-gray-400">Code: {{ $paint->product_code }}</div>
                                        </td>
                                        
                                        <!-- Specifications -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ ucfirst($paint->form) }}</div>
                                            @if(isset($paint->cmyk_c))
                                                <div class="text-xs text-gray-500">
                                                    CMYK: {{ $paint->cmyk_c ?? 0 }}, {{ $paint->cmyk_m ?? 0 }}, {{ $paint->cmyk_y ?? 0 }}, {{ $paint->cmyk_k ?? 0 }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    RGB: {{ $paint->rgb_r ?? 0 }}, {{ $paint->rgb_g ?? 0 }}, {{ $paint->rgb_b ?? 0 }}
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <!-- Price & Volume -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-lg font-bold text-green-600">£{{ number_format($paint->price_gbp, 2) }}</div>
                                            <div class="text-sm text-gray-500">
                                                @if($paint->volume_ml >= 1000)
                                                    {{ $paint->volume_ml / 1000 }}L
                                                @else
                                                    {{ $paint->volume_ml }}ml
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <!-- Admin Actions -->
                                        @if(auth()->user()->isAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('paints.show', $paint->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition-colors">
                                                    View
                                                </a>
                                                <a href="{{ route('paints.edit', $paint->id) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('paints.destroy', $paint->id) }}" 
                                                      class="inline" onsubmit="return confirm('Are you sure you want to delete this paint?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4">
                            @foreach($paints as $paint)
                            <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                                <div class="flex items-start space-x-4">
                                    <!-- Color Swatch -->
                                    <div class="w-16 h-16 rounded-lg border border-gray-300 flex-shrink-0" 
                                         style="background-color: {{ $paint->hex_color }}"></div>
                                    
                                    <!-- Paint Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $paint->product_name }}</h3>
                                        <p class="text-gray-600">{{ $paint->maker }}</p>
                                        <p class="text-sm text-gray-500">Code: {{ $paint->product_code }}</p>
                                        <p class="text-sm text-gray-500">{{ ucfirst($paint->form) }}</p>
                                        
                                        <div class="mt-2 flex justify-between items-center">
                                            <span class="text-lg font-bold text-green-600">£{{ number_format($paint->price_gbp, 2) }}</span>
                                            <span class="text-sm text-gray-500">
                                                @if($paint->volume_ml >= 1000)
                                                    {{ $paint->volume_ml / 1000 }}L
                                                @else
                                                    {{ $paint->volume_ml }}ml
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="text-xs text-gray-400 mt-1">{{ $paint->hex_color }}</div>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->isAdmin())
                                <!-- Mobile Actions -->
                                <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-4">
                                    <a href="{{ route('paints.show', $paint->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        View
                                    </a>
                                    <a href="{{ route('paints.edit', $paint->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('paints.destroy', $paint->id) }}" 
                                          class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination would go here when using real data -->
                        <div class="mt-6 text-center text-sm text-gray-500">
                            Showing {{ $paints->count() }} paints
                        </div>

                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-8 0 4 4 0 018 0zM7 21h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No paints found</h3>
                            @if(request('search'))
                                <p class="mt-1 text-sm text-gray-500">No paints match your search criteria.</p>
                                <div class="mt-6">
                                    <a href="{{ route('paints.index') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                                        View All Paints
                                    </a>
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Get started by adding your first paint to the catalog.</p>
                                @if(auth()->user()->isAdmin())
                                <div class="mt-6">
                                    <a href="{{ route('paints.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Add First Paint
                                    </a>
                                </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>