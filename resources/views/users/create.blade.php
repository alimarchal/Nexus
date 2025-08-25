<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Add New User
        </h2>
        
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
               
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Name:</label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Email:</label>
                        <input type="email" name="email" value="{{ old('email') }}" 
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Password:</label>
                        <input type="password" name="password" 
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Branch:</label>
                        <select name="branch_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Division:</label>
                        <select name="division_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Roles:</label>
                        <div class="mt-2">
                            @foreach($roles as $role)
                                <label class="inline-flex items-center mr-4 mb-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                        {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                        class="form-checkbox h-4 w-4 text-indigo-600">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Individual Permissions Section -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-3">Individual Permissions:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                            @foreach($permissions as $permission)
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300 text-sm">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-gray-500 dark:text-gray-400 mt-2 block">Note: Individual permissions are granted in addition to role-based permissions. Users will have access to permissions from both their assigned roles and individual permissions.</small>
                        @error('permissions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Super Admin:</label>
                        <select name="is_super_admin" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="No" {{ old('is_super_admin') == 'No' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ old('is_super_admin') == 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                        @error('is_super_admin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Status:</label>
                        <select name="is_active" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="Yes" {{ old('is_active') == 'Yes' ? 'selected' : '' }}>Active</option>
                            <option value="No" {{ old('is_active') == 'No' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition duration-200">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
