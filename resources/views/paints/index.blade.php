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
                
                <!-- CSV Import - temporarily available to all users for testing -->
                <button onclick="document.getElementById('csv-import').click()" 
                        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors"
                        title="Import paints from CSV file">
                    📋 CSV Import
                </button>
                <input type="file" id="csv-import" accept=".csv" style="display: none;" onchange="handleCSVImport(this)">
                
                <a href="{{ route('paint-bundles.index') }}" 
                   class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 transition-colors">
                    🎨 Paint Bundles
                </a>
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
                    
                    <!-- Enhanced Search and Filtering -->
                    <div class="mb-6 space-y-4">
                        <form method="GET" class="space-y-4">
                            <!-- Search Bar -->
                            <div class="flex gap-4">
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
                                @if(request('search') || request('manufacturer') || request('color_filter'))
                                    <a href="{{ route('paints.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                                        Clear All
                                    </a>
                                @endif
                            </div>

                            <!-- Filter Row -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Manufacturer Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                                    <select name="manufacturer" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                        <option value="">All Manufacturers</option>
                                        @php
                                            $manufacturers = collect($paints)->pluck('maker')->unique()->sort()->values();
                                        @endphp
                                        @foreach($manufacturers as $manufacturer)
                                            <option value="{{ $manufacturer }}" {{ request('manufacturer') == $manufacturer ? 'selected' : '' }}>
                                                {{ $manufacturer }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Color Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Color Family</label>
                                    <select name="color_filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                        <option value="">All Colors</option>
                                        <option value="red" {{ request('color_filter') == 'red' ? 'selected' : '' }}>Reds</option>
                                        <option value="blue" {{ request('color_filter') == 'blue' ? 'selected' : '' }}>Blues</option>
                                        <option value="green" {{ request('color_filter') == 'green' ? 'selected' : '' }}>Greens</option>
                                        <option value="yellow" {{ request('color_filter') == 'yellow' ? 'selected' : '' }}>Yellows</option>
                                        <option value="orange" {{ request('color_filter') == 'orange' ? 'selected' : '' }}>Oranges</option>
                                        <option value="purple" {{ request('color_filter') == 'purple' ? 'selected' : '' }}>Purples</option>
                                        <option value="pink" {{ request('color_filter') == 'pink' ? 'selected' : '' }}>Pinks</option>
                                        <option value="brown" {{ request('color_filter') == 'brown' ? 'selected' : '' }}>Browns</option>
                                        <option value="gray" {{ request('color_filter') == 'gray' ? 'selected' : '' }}>Grays</option>
                                        <option value="white" {{ request('color_filter') == 'white' ? 'selected' : '' }}>Whites</option>
                                        <option value="black" {{ request('color_filter') == 'black' ? 'selected' : '' }}>Blacks</option>
                                    </select>
                                </div>

                                <!-- Column Visibility Toggle -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Column Visibility</label>
                                    <button type="button" onclick="toggleColumnSettings()" 
                                            class="w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-left hover:bg-gray-200 transition-colors">
                                        <span class="flex items-center justify-between">
                                            <span>Customize Columns</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Column Settings Panel (Hidden by default) -->
                        <div id="column-settings" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3">Show/Hide Columns</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-color" checked class="mr-2" onchange="toggleColumn('color')">
                                    <span class="text-sm">Color Swatch</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-product" checked class="mr-2" onchange="toggleColumn('product')">
                                    <span class="text-sm">Product Details</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-specs" checked class="mr-2" onchange="toggleColumn('specs')">
                                    <span class="text-sm">Specifications</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-price" checked class="mr-2" onchange="toggleColumn('price')">
                                    <span class="text-sm">Price & Volume</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-description" class="mr-2" onchange="toggleColumn('description')">
                                    <span class="text-sm">Description</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-cmyk" class="mr-2" onchange="toggleColumn('cmyk')">
                                    <span class="text-sm">CMYK Values</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="col-rgb" class="mr-2" onchange="toggleColumn('rgb')">
                                    <span class="text-sm">RGB Values</span>
                                </label>
                            </div>
                        </div>

                        <!-- Bulk Actions Bar (visible to all users for bundles) -->
                        <div id="bulk-actions" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span id="selection-count" class="font-medium text-blue-800">0 paints selected</span>
                                    <button onclick="addToBundle()" 
                                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                                        🎨 Add to Bundle
                                    </button>
                                    @if(auth()->user()->isAdmin())
                                    <button onclick="bulkDelete()" 
                                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                        Delete Selected
                                    </button>
                                    @endif
                                </div>
                                <button onclick="clearSelection()" 
                                        class="text-blue-600 hover:text-blue-800 transition-colors">
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($paints->count() > 0)
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="paints-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left">
                                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-color">
                                            Color
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-product">
                                            Product Details
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-specs">
                                            Specifications
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-price">
                                            Price & Volume
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-description" style="display: none;">
                                            Description
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-cmyk" style="display: none;">
                                            CMYK
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-rgb" style="display: none;">
                                            RGB
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
                                        <!-- Checkbox -->
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="paint-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                                   value="{{ $paint->id }}" onchange="updateBulkActions()">
                                        </td>
                                        
                                        <!-- Color Swatch -->
                                        <td class="px-6 py-4 whitespace-nowrap column-color">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-sm" 
                                                     style="background-color: {{ $paint->hex_color }}"></div>
                                                <div class="ml-3">
                                                    <div class="text-xs font-mono text-gray-500">{{ $paint->hex_color }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Product Details -->
                                        <td class="px-6 py-4 column-product">
                                            <div class="text-sm font-medium text-gray-900">{{ $paint->product_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $paint->maker }}</div>
                                            <div class="text-xs text-gray-400">Code: {{ $paint->product_code }}</div>
                                        </td>
                                        
                                        <!-- Specifications -->
                                        <td class="px-6 py-4 column-specs">
                                            <div class="text-sm text-gray-900">{{ ucfirst($paint->form) }}</div>
                                        </td>
                                        
                                        <!-- Price & Volume -->
                                        <td class="px-6 py-4 whitespace-nowrap column-price">
                                            <div class="text-lg font-bold text-green-600">£{{ number_format($paint->price_gbp, 2) }}</div>
                                            <div class="text-sm text-gray-500">
                                                @if($paint->volume_ml >= 1000)
                                                    {{ $paint->volume_ml / 1000 }}L
                                                @else
                                                    {{ $paint->volume_ml }}ml
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Description (Hidden by default) -->
                                        <td class="px-6 py-4 column-description" style="display: none;">
                                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                                {{ $paint->color_description ?? 'No description' }}
                                            </div>
                                        </td>

                                        <!-- CMYK Values (Hidden by default) -->
                                        <td class="px-6 py-4 whitespace-nowrap column-cmyk" style="display: none;">
                                            @if(isset($paint->cmyk_c))
                                                <div class="text-xs text-gray-600">
                                                    C: {{ $paint->cmyk_c ?? 0 }}<br>
                                                    M: {{ $paint->cmyk_m ?? 0 }}<br>
                                                    Y: {{ $paint->cmyk_y ?? 0 }}<br>
                                                    K: {{ $paint->cmyk_k ?? 0 }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            @endif
                                        </td>

                                        <!-- RGB Values (Hidden by default) -->
                                        <td class="px-6 py-4 whitespace-nowrap column-rgb" style="display: none;">
                                            @if(isset($paint->rgb_r))
                                                <div class="text-xs text-gray-600">
                                                    R: {{ $paint->rgb_r ?? 0 }}<br>
                                                    G: {{ $paint->rgb_g ?? 0 }}<br>
                                                    B: {{ $paint->rgb_b ?? 0 }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            @endif
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

    <script>
        // Column visibility management
        function toggleColumnSettings() {
            const panel = document.getElementById('column-settings');
            panel.classList.toggle('hidden');
        }

        function toggleColumn(columnName) {
            const checkbox = document.getElementById(`col-${columnName}`);
            const columns = document.querySelectorAll(`.column-${columnName}`);
            
            columns.forEach(col => {
                col.style.display = checkbox.checked ? '' : 'none';
            });
        }

        // Bulk selection management
        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.paint-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.paint-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            const selectionCount = document.getElementById('selection-count');
            
            if (checkboxes.length > 0) {
                bulkActions.classList.remove('hidden');
                selectionCount.textContent = `${checkboxes.length} paint${checkboxes.length !== 1 ? 's' : ''} selected`;
            } else {
                bulkActions.classList.add('hidden');
            }
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.paint-checkbox');
            const selectAll = document.getElementById('select-all');
            
            checkboxes.forEach(cb => cb.checked = false);
            selectAll.checked = false;
            updateBulkActions();
        }

        function bulkDelete() {
            const checkboxes = document.querySelectorAll('.paint-checkbox:checked');
            
            if (checkboxes.length === 0) {
                alert('Please select at least one paint to delete.');
                return;
            }

            const count = checkboxes.length;
            if (!confirm(`Are you sure you want to delete ${count} selected paint${count !== 1 ? 's' : ''}? This action cannot be undone.`)) {
                return;
            }

            // Create form to submit bulk delete
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("paints.bulk-delete") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add selected paint IDs
            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'paint_ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Add to Bundle functionality
        function addToBundle() {
            const checkboxes = document.querySelectorAll('.paint-checkbox:checked');
            
            if (checkboxes.length === 0) {
                alert('Please select at least one paint to add to bundle.');
                return;
            }

            const paintIds = Array.from(checkboxes).map(cb => cb.value);
            
            // Store selected paints in session and redirect to bundle creation
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("paint-bundles.add-to-bundle") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add selected paint IDs
            paintIds.forEach(paintId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'paint_ids[]';
                input.value = paintId;
                form.appendChild(input);
            });

            // Add bundle name
            const bundleName = prompt(`Create new paint bundle with ${paintIds.length} paint${paintIds.length !== 1 ? 's' : ''}.\n\nBundle name:`, 'New Paint Bundle');
            if (!bundleName) return;

            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'bundle_name';
            nameInput.value = bundleName;
            form.appendChild(nameInput);

            // Add project selection (we'll improve this later with a modal)
            const projects = @json(collect(Session::get('projects', []))->values());
            if (projects.length === 0) {
                alert('Please create a project first before creating paint bundles.');
                return;
            }

            let projectOptions = 'Select a project for this bundle:\n\n';
            projects.forEach((project, index) => {
                projectOptions += `${index + 1}. ${project.name}\n`;
            });

            const projectChoice = prompt(projectOptions + '\nEnter project number:');
            if (!projectChoice || isNaN(projectChoice) || projectChoice < 1 || projectChoice > projects.length) {
                alert('Invalid project selection');
                return;
            }

            const projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'project_id';
            projectInput.value = projects[projectChoice - 1].id;
            form.appendChild(projectInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // CSV Import functionality
        function handleCSVImport(input) {
            if (!input.files || input.files.length === 0) return;
            
            const file = input.files[0];
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('Please select a valid CSV file.');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            const importBtn = document.querySelector('[onclick*="csv-import"]');
            const originalText = importBtn.textContent;
            importBtn.textContent = 'Importing...';
            importBtn.disabled = true;

            fetch('{{ route("paints.csv-import") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Import failed');
                }
            })
            .catch(error => {
                alert('Import failed: ' + error.message);
            })
            .finally(() => {
                importBtn.textContent = originalText;
                importBtn.disabled = false;
                input.value = ''; // Reset file input
            });
        }

        // Color filtering helper
        function getColorFamily(hexColor) {
            // Simple color family detection based on hex values
            const hex = hexColor.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            
            // Very basic color family detection
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            
            if (max - min < 50) {
                if (max < 100) return 'black';
                if (min > 200) return 'white';
                return 'gray';
            }
            
            if (r === max) {
                if (g > b) return g > 150 ? 'yellow' : 'red';
                return r > 200 && b > 150 ? 'pink' : 'red';
            }
            if (g === max) return b > r ? 'blue' : 'green';
            if (b === max) return r > g ? 'purple' : 'blue';
            
            return 'other';
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up any initial states
            updateBulkActions();
        });
    </script>
</x-app-layout>