<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Manager
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="{{ route('managers.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('managers.update', $manager->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Flex Container for Division & Manager -->
                    <div class="flex flex-wrap -mx-2">
                        <!-- Division Selection -->
                        <div class="w-full md:w-1/2 px-2 mb-4">
                            <label class="block text-gray-700">Division:</label>
                            <select name="division_id" class="select2 w-full border-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('division_id', $manager->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Manager Selection -->
                        <div class="w-full md:w-1/2 px-2 mb-4">
                            <label class="block text-gray-700">Manager:</label>
                            <select name="manager_user_id" class="select2 w-full border-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Manager</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('manager_user_id', $manager->manager_user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_user_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Title (Designation) -->
                    <div class="mb-4">
                        <label class="block text-gray-700">Title (Designation):</label>
                        <textarea id="description" name="title"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            rows="3">{{ old('title', $manager->title) }}</textarea>
                        @error('title')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
