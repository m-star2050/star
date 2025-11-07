<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Role Management - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background: url('{{ asset('image/Screenshot_16.png') }}') center center/cover no-repeat fixed !important;
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        .glass-card {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.25);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 10px 40px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.05);
        }
        .glass {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1), inset 0 1px 0 0 rgba(255,255,255,0.2);
        }
        .role-select {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.9);
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 0.75rem;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
            transition: all 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.25rem;
            cursor: pointer;
        }
        .role-select:hover {
            border-color: rgba(59, 130, 246, 0.5);
            background-color: rgba(255,255,255,1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        .role-select:focus {
            outline: none;
            border-color: #3b82f6;
            ring: 2px;
            ring-color: rgba(59, 130, 246, 0.2);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .table-header {
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .table-row {
            background: rgba(255,255,255,0.15);
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .back-button {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.3);
            border: 2px solid rgba(255,255,255,0.4);
            transition: all 0.2s ease;
        }
        .back-button:hover {
            background: rgba(255,255,255,0.5);
            border-color: rgba(255,255,255,0.6);
            transform: translateX(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .notification {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="min-h-screen px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Back Button and Header -->
            <div class="mb-6 flex items-center gap-4">
                <a href="{{ route('crm.contacts.index') }}" class="back-button inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-gray-700 font-semibold shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to CRM</span>
                </a>
            </div>

            <!-- Header Card -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            User Role Management
                        </h1>
                        <p class="text-gray-600 font-medium">Manage user roles and permissions across your CRM system</p>
                    </div>
                </div>
            </div>

            @if(!$packageInstalled)
                <div class="glass-card rounded-2xl p-5 mb-6 border-l-4 border-yellow-400 bg-yellow-50/50">
                    <div class="flex items-start gap-3">
                        <svg class="h-6 w-6 text-yellow-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-bold text-yellow-800 mb-1">Spatie Permission Package Required</h3>
                            <p class="text-sm text-yellow-700 mb-2">
                                Please install the Spatie Laravel Permission package to manage user roles:
                            </p>
                            <code class="bg-yellow-100 px-3 py-1.5 rounded-lg text-xs font-mono text-yellow-900 border border-yellow-200">
                                composer require spatie/laravel-permission
                            </code>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Users Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/20">
                        <thead class="table-header">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        User
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Email
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Current Role
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        </svg>
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/10 divide-y divide-white/20">
                            @forelse($users as $user)
                                <tr class="table-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold shadow-lg">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $user->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700 font-medium">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($packageInstalled && $user->roles->isNotEmpty())
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold shadow-md
                                                    @if($role->name === 'Admin') bg-gradient-to-r from-purple-500 to-purple-600 text-white
                                                    @elseif($role->name === 'Manager') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                                    @else bg-gradient-to-r from-green-500 to-green-600 text-white
                                                    @endif">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                No role assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($packageInstalled)
                                            <select 
                                                class="role-select"
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
                                            <span class="text-sm text-gray-400 font-medium">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <p class="text-sm font-semibold text-gray-500">No users found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Guide Card -->
            <div class="glass-card rounded-2xl p-6 mt-6 border-l-4 border-blue-500">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-3">Quick Guide</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white/30 rounded-xl p-4 backdrop-blur-sm">
                                <div class="flex items-start gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 mb-1">First User</p>
                                        <p class="text-sm text-gray-600">Automatically gets <span class="font-bold text-purple-600">Admin</span> role on registration</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/30 rounded-xl p-4 backdrop-blur-sm">
                                <div class="flex items-start gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 mb-1">Subsequent Users</p>
                                        <p class="text-sm text-gray-600">Automatically get <span class="font-bold text-green-600">Executive</span> role on registration</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/30 rounded-xl p-4 backdrop-blur-sm">
                                <div class="flex items-start gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 mb-1">Change Roles</p>
                                        <p class="text-sm text-gray-600">Use the dropdown above to assign Admin, Manager, or Executive roles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/30 rounded-xl p-4 backdrop-blur-sm">
                                <div class="flex items-start gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 mb-1">Alternative Method</p>
                                        <p class="text-sm text-gray-600">Use Laravel Tinker for programmatic role assignment</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="fixed top-4 right-4 z-50 hidden notification glass-card rounded-xl p-4 shadow-2xl max-w-md">
        <div class="flex items-center gap-3">
            <div id="notification-icon" class="flex-shrink-0"></div>
            <div class="flex-1">
                <p id="notification-message" class="text-sm font-semibold text-gray-800"></p>
            </div>
            <button onclick="hideNotification()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const icon = document.getElementById('notification-icon');
            const messageEl = document.getElementById('notification-message');
            
            // Set icon based on type
            if (type === 'success') {
                icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
                notification.classList.remove('border-red-500', 'border-yellow-500');
                notification.classList.add('border-green-500');
            } else if (type === 'error') {
                icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
                notification.classList.remove('border-green-500', 'border-yellow-500');
                notification.classList.add('border-red-500');
            }
            
            messageEl.textContent = message;
            notification.classList.remove('hidden');
            notification.classList.add('border-l-4');
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                hideNotification();
            }, 5000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.classList.add('hidden');
        }

        function updateUserRole(userId, roleName) {
            if (!roleName) {
                return;
            }

            const selectElement = event.target;
            const originalValue = selectElement.dataset.originalValue || selectElement.selectedIndex === 0 ? '' : Array.from(selectElement.options).find(opt => opt.selected).value;
            selectElement.dataset.originalValue = roleName;

            if (!confirm(`Are you sure you want to change this user's role to ${roleName}?`)) {
                // Reset dropdown to original value
                selectElement.value = originalValue || '';
                return;
            }

            // Disable select during request
            selectElement.disabled = true;
            selectElement.style.opacity = '0.6';
            selectElement.style.cursor = 'not-allowed';

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
                    showNotification(data.message || 'User role updated successfully!', 'success');
                    // Reload after a short delay to show the notification
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Error updating user role.', 'error');
                    // Reset dropdown on error
                    selectElement.value = originalValue || '';
                    selectElement.disabled = false;
                    selectElement.style.opacity = '1';
                    selectElement.style.cursor = 'pointer';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while updating the user role.', 'error');
                // Reset dropdown on error
                selectElement.value = originalValue || '';
                selectElement.disabled = false;
                selectElement.style.opacity = '1';
                selectElement.style.cursor = 'pointer';
            });
        }
    </script>
</body>
</html>
