<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $paint->product_name }}
            </h2>
            @if(auth()->user()->isAdmin())
                <div class="flex gap-2">
                    <a href="{{ route('paints.edit', $paint->id) }}" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Edit Paint
                    </a>
                    <form method="POST" action="{{ route('paints.destroy', $paint->id) }}" 
                          class="inline" onsubmit="return confirm('Are you sure you want to delete this paint?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                            Delete Paint
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
                        <!-- Color Display -->
                        <div>
                            <div class="w-full h-64 rounded-lg border border-gray-300 shadow-lg mb-4" 
                                 style="background-color: {{ $paint->hex_color }}"></div>
                            <div class="text-center">
                                <span class="text-lg font-mono bg-gray-100 px-3 py-1 rounded">{{ $paint->hex_color }}</span>
                            </div>
                        </div>

                        <!-- Paint Details -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Product Name:</dt>
                                        <dd class="font-medium">{{ $paint->product_name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Manufacturer:</dt>
                                        <dd class="font-medium">{{ $paint->maker }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Product Code:</dt>
                                        <dd class="font-medium">{{ $paint->product_code }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Form:</dt>
                                        <dd class="font-medium">{{ ucfirst($paint->form) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Volume:</dt>
                                        <dd class="font-medium">
                                            @if($paint->volume_ml >= 1000)
                                                {{ $paint->volume_ml / 1000 }}L
                                            @else
                                                {{ $paint->volume_ml }}ml
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Price:</dt>
                                        <dd class="text-xl font-bold text-green-600">£{{ number_format($paint->price_gbp, 2) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Color Values -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Color Values</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    <div class="bg-gray-50 p-3 rounded">
                                        <div class="text-sm text-gray-600">HEX</div>
                                        <div class="font-mono">{{ $paint->hex_color }}</div>
                                    </div>
                                    @if(isset($paint->rgb_r))
                                    <div class="bg-gray-50 p-3 rounded">
                                        <div class="text-sm text-gray-600">RGB</div>
                                        <div class="font-mono">{{ $paint->rgb_r }}, {{ $paint->rgb_g }}, {{ $paint->rgb_b }}</div>
                                    </div>
                                    @endif
                                    @if(isset($paint->cmyk_c))
                                    <div class="bg-gray-50 p-3 rounded">
                                        <div class="text-sm text-gray-600">CMYK</div>
                                        <div class="font-mono">{{ $paint->cmyk_c }}%, {{ $paint->cmyk_m }}%, {{ $paint->cmyk_y }}%, {{ $paint->cmyk_k }}%</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($paint->color_description)
                            <!-- Color Description -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Description</h4>
                                <p class="text-gray-700 leading-relaxed">{{ $paint->color_description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('paints.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            ← Back to Paint Catalog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>