<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Role Management - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">User Role Management</h1>
                <p class="text-gray-600">Manage user roles and permissions</p>
            </div>

            @if(!$packageInstalled)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Spatie Permission package is not installed.</strong> Please install it to manage user roles:
                                <code class="bg-yellow-100 px-2 py-1 rounded text-xs">composer require spatie/laravel-permission</code>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($packageInstalled && $user->roles->isNotEmpty())
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($role->name === 'Admin') bg-purple-100 text-purple-800
                                                    @elseif($role->name === 'Manager') bg-blue-100 text-blue-800
                                                    @else bg-green-100 text-green-800
                                                    @endif">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-sm text-gray-400">No role assigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($packageInstalled)
                                            <select 
                                                class="role-select border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                data-user-id="{{ $user->id }}"
                                                data-user-email="{{ $user->email }}"
                                                onchange="updateUserRole({{ $user->id }}, this.value)">
                                                <option value="">-- Select Role --</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" 
                                                        {{ $user->roles->contains($role) ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <h3 class="text-sm font-medium text-blue-800 mb-2">Quick Guide:</h3>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                    <li><strong>First User:</strong> Automatically gets <strong>Admin</strong> role on registration</li>
                    <li><strong>Subsequent Users:</strong> Automatically get <strong>Executive</strong> role on registration</li>
                    <li><strong>Change Roles:</strong> Use the dropdown above to assign Admin, Manager, or Executive roles</li>
                    <li><strong>Alternative Method:</strong> Use Tinker (see instructions below)</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function updateUserRole(userId, roleName) {
            if (!roleName) {
                return;
            }

            if (!confirm(`Are you sure you want to change this user's role to ${roleName}?`)) {
                // Reset dropdown to original value
                location.reload();
                return;
            }

            fetch(`{{ route('crm.user-roles.update', '__USER_ID__') }}`.replace('__USER_ID__', userId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    role: roleName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'User role updated successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Error updating user role.');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the user role.');
                location.reload();
            });
        }
    </script>
</body>
</html>

