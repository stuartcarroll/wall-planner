<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Paint: {{ $paint->product_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Edit Paint Details</h3>
                    
                    <form method="POST" action="{{ route('paints.update', $paint->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Product Name -->
                        <div>
                            <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="product_name" id="product_name" required
                                   value="{{ old('product_name', $paint->product_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('product_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Product Code and Maker -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="product_code" class="block text-sm font-medium text-gray-700">Product Code</label>
                                <input type="text" name="product_code" id="product_code" required
                                       value="{{ old('product_code', $paint->product_code) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('product_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="maker" class="block text-sm font-medium text-gray-700">Maker</label>
                                <input type="text" name="maker" id="maker" required
                                       value="{{ old('maker', $paint->maker) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('maker')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- CMYK Values -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CMYK Color Values (0-100)</label>
                            <div class="grid grid-cols-4 gap-4">
                                <div>
                                    <label for="cmyk_c" class="block text-xs text-gray-500">Cyan (C)</label>
                                    <input type="number" name="cmyk_c" id="cmyk_c" min="0" max="100" required
                                           value="{{ old('cmyk_c', $paint->cmyk_c ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="cmyk_m" class="block text-xs text-gray-500">Magenta (M)</label>
                                    <input type="number" name="cmyk_m" id="cmyk_m" min="0" max="100" required
                                           value="{{ old('cmyk_m', $paint->cmyk_m ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="cmyk_y" class="block text-xs text-gray-500">Yellow (Y)</label>
                                    <input type="number" name="cmyk_y" id="cmyk_y" min="0" max="100" required
                                           value="{{ old('cmyk_y', $paint->cmyk_y ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="cmyk_k" class="block text-xs text-gray-500">Key/Black (K)</label>
                                    <input type="number" name="cmyk_k" id="cmyk_k" min="0" max="100" required
                                           value="{{ old('cmyk_k', $paint->cmyk_k ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- RGB Values -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">RGB Color Values (0-255)</label>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="rgb_r" class="block text-xs text-gray-500">Red (R)</label>
                                    <input type="number" name="rgb_r" id="rgb_r" min="0" max="255" required
                                           value="{{ old('rgb_r', $paint->rgb_r ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="rgb_g" class="block text-xs text-gray-500">Green (G)</label>
                                    <input type="number" name="rgb_g" id="rgb_g" min="0" max="255" required
                                           value="{{ old('rgb_g', $paint->rgb_g ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="rgb_b" class="block text-xs text-gray-500">Blue (B)</label>
                                    <input type="number" name="rgb_b" id="rgb_b" min="0" max="255" required
                                           value="{{ old('rgb_b', $paint->rgb_b ?? 0) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Hex Color -->
                        <div>
                            <label for="hex_color" class="block text-sm font-medium text-gray-700">Hex Color</label>
                            <div class="mt-1 flex">
                                <input type="text" name="hex_color" id="hex_color" required
                                       value="{{ old('hex_color', $paint->hex_color) }}"
                                       pattern="^#[A-Fa-f0-9]{6}$"
                                       class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <div id="color_preview" class="w-12 border border-l-0 border-gray-300 rounded-r-md"
                                     style="background-color: {{ old('hex_color', $paint->hex_color) }}"></div>
                            </div>
                            @error('hex_color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form and Volume -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="form" class="block text-sm font-medium text-gray-700">Paint Form</label>
                                <select name="form" id="form" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select paint type</option>
                                    <option value="spray" {{ old('form', $paint->form) == 'spray' ? 'selected' : '' }}>Spray Paint</option>
                                    <option value="emulsion" {{ old('form', $paint->form) == 'emulsion' ? 'selected' : '' }}>Emulsion</option>
                                    <option value="estate emulsion" {{ old('form', $paint->form) == 'estate emulsion' ? 'selected' : '' }}>Estate Emulsion</option>
                                    <option value="modern emulsion" {{ old('form', $paint->form) == 'modern emulsion' ? 'selected' : '' }}>Modern Emulsion</option>
                                    <option value="matt emulsion" {{ old('form', $paint->form) == 'matt emulsion' ? 'selected' : '' }}>Matt Emulsion</option>
                                    <option value="silk emulsion" {{ old('form', $paint->form) == 'silk emulsion' ? 'selected' : '' }}>Silk Emulsion</option>
                                    <option value="intelligent matt emulsion" {{ old('form', $paint->form) == 'intelligent matt emulsion' ? 'selected' : '' }}>Intelligent Matt Emulsion</option>
                                    <option value="advance paint" {{ old('form', $paint->form) == 'advance paint' ? 'selected' : '' }}>Advance Paint</option>
                                    <option value="acrylic" {{ old('form', $paint->form) == 'acrylic' ? 'selected' : '' }}>Acrylic</option>
                                    <option value="gloss" {{ old('form', $paint->form) == 'gloss' ? 'selected' : '' }}>Gloss</option>
                                    <option value="matt" {{ old('form', $paint->form) == 'matt' ? 'selected' : '' }}>Matt</option>
                                    <option value="satin" {{ old('form', $paint->form) == 'satin' ? 'selected' : '' }}>Satin</option>
                                </select>
                                @error('form')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="volume_ml" class="block text-sm font-medium text-gray-700">Volume (ml)</label>
                                <input type="number" name="volume_ml" id="volume_ml" min="1" required
                                       value="{{ old('volume_ml', $paint->volume_ml) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('volume_ml')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price_gbp" class="block text-sm font-medium text-gray-700">Price (GBP £)</label>
                            <input type="number" name="price_gbp" id="price_gbp" step="0.01" min="0" required
                                   value="{{ old('price_gbp', $paint->price_gbp) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('price_gbp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Color Description -->
                        <div>
                            <label for="color_description" class="block text-sm font-medium text-gray-700">Color Description (Optional)</label>
                            <textarea name="color_description" id="color_description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="A beautiful description of this color...">{{ old('color_description', $paint->color_description) }}</textarea>
                            @error('color_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-between">
                            <a href="{{ route('paints.show', $paint->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                ← Cancel
                            </a>
                            
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Update Paint
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update color preview when hex color changes
        document.getElementById('hex_color').addEventListener('input', function() {
            const hexValue = this.value;
            const preview = document.getElementById('color_preview');
            if (hexValue.match(/^#[A-Fa-f0-9]{6}$/)) {
                preview.style.backgroundColor = hexValue;
            }
        });
    </script>
</x-app-layout>