<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Action Bar -->
            <div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    
                    <!-- Left side: Bulk Actions -->
                    <div>
                        <div id="bulk-actions" style="display: none; background: #fef2f2; border: 1px solid #fecaca; padding: 10px; border-radius: 6px;">
                            <span id="selection-info" style="margin-right: 15px; color: #dc2626; font-weight: 500;">0 projects selected</span>
                            <button onclick="bulkDelete()" style="background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-right: 10px; cursor: pointer;">
                                Delete Selected
                            </button>
                            <button onclick="clearSelection()" style="background: transparent; color: #dc2626; border: 1px solid #dc2626; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                Clear Selection
                            </button>
                        </div>
                        <div id="no-selection" style="color: #6b7280;">Select projects to perform bulk actions</div>
                    </div>
                    
                    <!-- Right side: Create Button -->
                    <div>
                        <a href="{{ route('projects.create') }}" 
                           style="background: #16a34a; color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: 500; display: inline-block;">
                            ➕ Create New Project
                        </a>
                    </div>
                    
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Projects Content -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                
                @if($projects->count() > 0)
                    
                    <!-- Projects Table -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                            <tr>
                                <th style="padding: 15px; text-align: left; width: 50px;">
                                    <input type="checkbox" id="select-all" onchange="toggleAll(this)" 
                                           style="width: 16px; height: 16px;">
                                </th>
                                <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Project</th>
                                <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Location</th>
                                <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Owner</th>
                                <th style="padding: 15px; text-align: left; font-weight: 600; color: #374151;">Created</th>
                                <th style="padding: 15px; text-align: center; font-weight: 600; color: #374151; width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 15px;">
                                    <input type="checkbox" class="project-checkbox" value="{{ $project->id }}" 
                                           onchange="updateBulkActions()" style="width: 16px; height: 16px;">
                                </td>
                                <td style="padding: 15px;">
                                    <div>
                                        <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $project->name }}</div>
                                        <div style="color: #6b7280; font-size: 14px;">{{ Str::limit($project->description, 60) }}</div>
                                    </div>
                                </td>
                                <td style="padding: 15px; color: #374151;">{{ $project->location }}</td>
                                <td style="padding: 15px; color: #374151;">{{ $project->owner_name }}</td>
                                <td style="padding: 15px; color: #6b7280;">{{ $project->created_at->format('M j, Y') }}</td>
                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        
                                        <!-- View Button -->
                                        <a href="{{ route('projects.show', $project) }}" 
                                           style="background: #3b82f6; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px;">
                                            View
                                        </a>
                                        
                                        <!-- Edit Button (if user has permission) -->
                                        @if(auth()->user()->isAdmin() || $project->owner_id == auth()->id() || (!empty($project->project_manager_email) && $project->project_manager_email === auth()->user()->email))
                                            <a href="{{ route('projects.edit', $project) }}" 
                                               style="background: #059669; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px;">
                                                Edit
                                            </a>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" action="{{ route('projects.destroy', $project) }}" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete {{ $project->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                        
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                @else
                    
                    <!-- Empty State -->
                    <div style="padding: 60px 20px; text-align: center;">
                        <div style="color: #6b7280; font-size: 18px; margin-bottom: 10px;">📋</div>
                        <h3 style="color: #374151; font-size: 18px; margin-bottom: 8px;">No projects yet</h3>
                        <p style="color: #6b7280; margin-bottom: 20px;">
                            @if(auth()->user()->isAdmin())
                                No projects have been created yet.
                            @else
                                You haven't created any projects yet.
                            @endif
                        </p>
                        <a href="{{ route('projects.create') }}" 
                           style="background: #16a34a; color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: 500;">
                            ➕ Create Your First Project
                        </a>
                    </div>
                    
                @endif
                
            </div>
        </div>
    </div>

    <!-- JavaScript for Bulk Actions -->
    <script>
        function toggleAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            const checkedBoxes = document.querySelectorAll('.project-checkbox:checked');
            const count = checkedBoxes.length;
            
            const bulkActions = document.getElementById('bulk-actions');
            const noSelection = document.getElementById('no-selection');
            const selectionInfo = document.getElementById('selection-info');
            
            if (count > 0) {
                bulkActions.style.display = 'block';
                noSelection.style.display = 'none';
                selectionInfo.textContent = `${count} project${count !== 1 ? 's' : ''} selected`;
            } else {
                bulkActions.style.display = 'none';
                noSelection.style.display = 'block';
            }
            
            // Update master checkbox state
            const masterCheckbox = document.getElementById('select-all');
            if (count === 0) {
                masterCheckbox.indeterminate = false;
                masterCheckbox.checked = false;
            } else if (count === checkboxes.length) {
                masterCheckbox.indeterminate = false;
                masterCheckbox.checked = true;
            } else {
                masterCheckbox.indeterminate = true;
                masterCheckbox.checked = false;
            }
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            const masterCheckbox = document.getElementById('select-all');
            
            checkboxes.forEach(cb => cb.checked = false);
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
            
            updateBulkActions();
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.project-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                alert('Please select at least one project to delete.');
                return;
            }

            const count = checkedBoxes.length;
            if (!confirm(`Are you sure you want to delete ${count} selected project${count !== 1 ? 's' : ''}? This action cannot be undone.`)) {
                return;
            }

            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("projects.bulk-delete") }}';
            
            // CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Project IDs
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'project_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateBulkActions();
        });
    </script>

</x-app-layout>