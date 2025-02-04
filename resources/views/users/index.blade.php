<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Settings User-Module Users
        </h2>
        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bbg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>

            <a href="{{ route('users.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add User</span>
            </a>
            <a href="{{ route('user.module') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('users.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Branch Filter -->
                        <div>
                            <x-label for="branch_id" value="{{ __('Branch') }}" />
                            <select name="filter[branch_id]" id="branch_id"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a branch</option>
                                @foreach (\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ request('filter.branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->code . ' - ' . $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <x-label for="role" value="{{ __('Role') }}" />
                            <select name="filter[role]" id="role"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select a role</option>
                                @foreach (\Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->name }}"
                                        {{ request('filter.role') == $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <x-label for="is_active" value="{{ __('Status') }}" />
                            <select name="filter[is_active]" id="is_active"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select status</option>
                                <option value="1" {{ request('filter.is_active') == '1' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="0" {{ request('filter.is_active') == '0' ? 'selected' : '' }}>
                                    Inactive</option>
                            </select>
                        </div>


                        <!-- Created At Filter -->
                        <div>
                            <x-label for="created_at" value="{{ __('Created Date') }}" />
                            <input type="date" name="filter[created_at]" id="created_at"
                                value="{{ request('filter.created_at') }}"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button class="mc-bg-blue text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-status-message />
                @if ($users->count() > 0)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-blue-800 text-white uppercase text-sm">
                                    <th class="py-2 px-4 text-center">Name</th>
                                    <th class="py-2 px-4 text-center">Email</th>
                                    <th class="py-2 px-4 text-center">Branch</th>
                                    {{--  <th class="py-2 px-4 text-center">Super Admin</th>  --}}
                                    {{--  <th class="py-2 px-4 text-center">Pssword</th>  --}}
                                    <th class="py-2 px-4 text-center">Active</th>
                                    <th class="py-2 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-black text-md leading-normal font-extrabold">
                                @foreach ($users as $user)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-1 px-4 text-center">{{ $user->name }}</td>
                                        <td class="py-1 px-4 text-center">{{ $user->email }}</td>
                                        <td class="py-1 px-4 text-center">{{ $user->branch->name ?? 'N/A' }}</td>
                                        {{--  <td class="py-1 px-4 text-center">{{ $user->is_super_admin ? 'Yes' : 'No' }}</td>  --}}
                                        {{--  <td class="py-1 px-4 text-center">{{ $user->password }}</td>  --}}
                                        <td class="py-1 px-4 text-center">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="py-1 px-4 text-center flex space-x-2">
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="px-4 py-2 text-white bg-green-800 hover:bg-green-700 rounded-md">Edit</a>
                                            {{--  <form action="{{ route('users.destroy', $user) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-md">Delete</button>
                                            </form>  --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                @else
                    <p class="text-center py-6">No users found. <a href="{{ route('users.create') }}"
                            class="text-blue-600 hover:underline">Add a new user</a>.</p>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Submit the form if confirmed
                        }
                    });
                });
            });
        </script>
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");

            function showFilters() {
                targetDiv.style.display = 'block';
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.opacity = '1';
                    targetDiv.style.transform = 'translateY(0)';
                }, 10);
            }

            function hideFilters() {
                targetDiv.style.opacity = '0';
                targetDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    targetDiv.style.display = 'none';
                }, 300);
            }

            btn.onclick = function(event) {
                event.stopPropagation();
                if (targetDiv.style.display === "none") {
                    showFilters();
                } else {
                    hideFilters();
                }
            };

            // Hide filters when clicking outside
            document.addEventListener('click', function(event) {
                if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn) {
                    hideFilters();
                }
            });

            // Prevent clicks inside the filter from closing it
            targetDiv.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            // Add CSS for smooth transitions
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
        </script>
    @endpush

</x-app-layout>
