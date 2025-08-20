<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Paint Bundle
            </h2>
            <a href="{{ route('paint-bundles.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                Back to Bundles
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('paint-bundles.store') }}">
                @csrf
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        
                        <!-- Bundle Details -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bundle Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', session('selected_paints_for_bundle.bundle_name', '')) }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="e.g., Living Room Walls, Kitchen Cabinet Paint"
                                       required>
                                @error('name')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Project <span class="text-red-500">*</span>
                                </label>
                                <select name="project_id" id="project_id" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        required>
                                    <option value="">Select a project...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ old('project_id', $selectedProjectId ?? session('selected_paints_for_bundle.project_id')) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-8">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Describe the purpose of this paint bundle...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Paint Selection -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Paint Selection</h3>
                                <button type="button" onclick="addPaintRow()" 
                                        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors">
                                    Add Paint
                                </button>
                            </div>

                            <div id="paint-items" class="space-y-4">
                                @php
                                    $selectedPaints = session('selected_paints_for_bundle.paints', []);
                                @endphp
                                
                                @if(count($selectedPaints) > 0)
                                    @foreach($selectedPaints as $index => $paint)
                                    <div class="paint-item bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Paint</label>
                                                <select name="paint_items[{{ $index }}][paint_id]" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                                                    <option value="">Select paint...</option>
                                                    @foreach($paints as $paintOption)
                                                        <option value="{{ $paintOption->id }}" 
                                                                {{ $paint['id'] == $paintOption->id ? 'selected' : '' }}
                                                                data-price="{{ $paintOption->price_gbp }}"
                                                                data-volume="{{ $paintOption->volume_ml }}">
                                                            {{ $paintOption->product_name }} - {{ $paintOption->maker }} (£{{ number_format($paintOption->price_gbp, 2) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                                <input type="number" 
                                                       name="paint_items[{{ $index }}][quantity]" 
                                                       value="1" min="1" 
                                                       class="w-full border border-gray-300 rounded-md px-3 py-2" 
                                                       required onchange="updateCosts()">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                                                <div class="text-lg font-bold text-green-600">£{{ number_format($paint['price_gbp'], 2) }}</div>
                                            </div>
                                            <div>
                                                <button type="button" onclick="removePaintRow(this)" 
                                                        class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition-colors">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="paint-item bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Paint</label>
                                                <select name="paint_items[0][paint_id]" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                                                    <option value="">Select paint...</option>
                                                    @foreach($paints as $paint)
                                                        <option value="{{ $paint->id }}" 
                                                                data-price="{{ $paint->price_gbp }}"
                                                                data-volume="{{ $paint->volume_ml }}">
                                                            {{ $paint->product_name }} - {{ $paint->maker }} (£{{ number_format($paint->price_gbp, 2) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                                <input type="number" 
                                                       name="paint_items[0][quantity]" 
                                                       value="1" min="1" 
                                                       class="w-full border border-gray-300 rounded-md px-3 py-2" 
                                                       required onchange="updateCosts()">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                                                <div class="text-lg font-bold text-green-600">£0.00</div>
                                            </div>
                                            <div>
                                                <button type="button" onclick="removePaintRow(this)" 
                                                        class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition-colors">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Cost Summary -->
                        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h4 class="font-medium text-blue-900 mb-3">Bundle Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600" id="total-items">{{ count($selectedPaints) }}</div>
                                    <div class="text-sm text-blue-600">Paint Items</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600" id="total-cost">£0.00</div>
                                    <div class="text-sm text-green-600">Total Cost</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600" id="total-volume">0L</div>
                                    <div class="text-sm text-purple-600">Total Volume</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                Create Paint Bundle
                            </button>
                            <a href="{{ route('paint-bundles.index') }}" 
                               class="bg-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                                Cancel
                            </a>
                            <a href="{{ route('paints.index') }}" 
                               class="bg-purple-500 text-white px-6 py-3 rounded-md hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                                Browse Paint Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = {{ count($selectedPaints) > 0 ? count($selectedPaints) : 1 }};

        function addPaintRow() {
            const container = document.getElementById('paint-items');
            const newRow = document.createElement('div');
            newRow.className = 'paint-item bg-gray-50 p-4 rounded-lg';
            
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paint</label>
                        <select name="paint_items[${itemIndex}][paint_id]" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                            <option value="">Select paint...</option>
                            @foreach($paints as $paint)
                                <option value="{{ $paint->id }}" 
                                        data-price="{{ $paint->price_gbp }}"
                                        data-volume="{{ $paint->volume_ml }}">
                                    {{ $paint->product_name }} - {{ $paint->maker }} (£{{ number_format($paint->price_gbp, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" 
                               name="paint_items[${itemIndex}][quantity]" 
                               value="1" min="1" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2" 
                               required onchange="updateCosts()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                        <div class="text-lg font-bold text-green-600">£0.00</div>
                    </div>
                    <div>
                        <button type="button" onclick="removePaintRow(this)" 
                                class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition-colors">
                            Remove
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(newRow);
            itemIndex++;
            updateCosts();
        }

        function removePaintRow(button) {
            const paintItems = document.querySelectorAll('.paint-item');
            if (paintItems.length > 1) {
                button.closest('.paint-item').remove();
                updateCosts();
            } else {
                alert('At least one paint item is required.');
            }
        }

        function updateCosts() {
            const paintItems = document.querySelectorAll('.paint-item');
            let totalCost = 0;
            let totalVolume = 0;
            let totalItems = 0;

            paintItems.forEach(item => {
                const select = item.querySelector('select');
                const quantity = parseInt(item.querySelector('input[type="number"]').value) || 0;
                const priceDisplay = item.querySelector('.text-green-600');
                
                if (select.value && quantity > 0) {
                    const option = select.selectedOptions[0];
                    const unitPrice = parseFloat(option.dataset.price) || 0;
                    const unitVolume = parseInt(option.dataset.volume) || 0;
                    
                    priceDisplay.textContent = `£${unitPrice.toFixed(2)}`;
                    totalCost += unitPrice * quantity;
                    totalVolume += unitVolume * quantity;
                    totalItems++;
                } else {
                    priceDisplay.textContent = '£0.00';
                }
            });

            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('total-cost').textContent = `£${totalCost.toFixed(2)}`;
            
            if (totalVolume >= 1000) {
                document.getElementById('total-volume').textContent = `${(totalVolume / 1000).toFixed(1)}L`;
            } else {
                document.getElementById('total-volume').textContent = `${totalVolume}ml`;
            }
        }

        // Update costs when paint selection changes
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' || e.target.type === 'number') {
                updateCosts();
            }
        });

        // Initial cost calculation
        document.addEventListener('DOMContentLoaded', function() {
            updateCosts();
        });
    </script>
</x-app-layout>