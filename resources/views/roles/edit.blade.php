<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Edit Role
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="{{ route('roles.index') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <!-- Display session message -->
                <x-status-message class="mb-4 mt-4" />

                <x-validation-errors class="mb-4 mt-4" />

                <form method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="name" value="Name" :required="true" />
                            <x-input id="name" type="text" name="name" class="mt-1 block w-full" :value="old('name', $role->name)" required />
                        </div>

                        <div>
                            <x-label for="guard_name" value="Guard Name" :required="true" />
                            <x-input id="guard_name" type="text" name="guard_name" class="mt-1 block w-full" :value="old('guard_name', $role->guard_name)" required />
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="mt-6">
                        <x-label value="Permissions" class="mb-3" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto bg-gray-50 dark:bg-gray-700 p-4 rounded-md border">
                            @foreach($permissions as $permission)
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300 text-sm">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-gray-500 dark:text-gray-400 mt-2 block">Modify permissions assigned to this role. Changes will affect all users with this role.</small>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            Update
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
