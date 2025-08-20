<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manage Users: {{ $userGroup->name }}
            </h2>
            <a href="{{ route('admin.user-groups.show', $userGroup) }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Back to Group
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="text-green-800">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="text-red-800">{{ session('error') }}</div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Add Users Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Users to Group</h3>
                        
                        @php
                            $currentUserIds = $userGroup->users->pluck('id')->toArray();
                            $availableUsers = $users->whereNotIn('id', $currentUserIds);
                        @endphp
                        
                        @if($availableUsers->count() > 0)
                            <form method="POST" action="{{ route('admin.user-groups.add-user', $userGroup) }}" class="mb-6">
                                @csrf
                                <div class="flex gap-3">
                                    <select name="user_id" class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Select a user...</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                        Add User
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">All users are in this group</h3>
                                <p class="mt-1 text-sm text-gray-500">Every user in the system is already a member of this group.</p>
                            </div>
                        @endif

                        <!-- Available Users List -->
                        @if($availableUsers->count() > 0)
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Available Users ({{ $availableUsers->count() }})</h4>
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @foreach($availableUsers as $user)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                            <form method="POST" action="{{ route('admin.user-groups.add-user', $userGroup) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Add
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Current Members Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Members ({{ $userGroup->users->count() }})</h3>
                        
                        @if($userGroup->users->count() > 0)
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @foreach($userGroup->users as $user)
                                    <div class="flex justify-between items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            <div class="text-xs text-blue-600 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.user-groups.remove-user', $userGroup) }}" 
                                              class="inline" onsubmit="return confirm('Remove {{ $user->name }} from this group?');">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No members yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Start adding users to this group using the form on the left.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>