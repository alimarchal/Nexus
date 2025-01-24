<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add New User
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700">Name:</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Email:</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Password:</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Branch:</label>
                        <select name="branch_id" class="w-full border-gray-300 rounded-md shadow-sm">
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
                        <label class="block text-gray-700">Super Admin:</label>
                        <select name="is_super_admin" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="No" {{ old('is_super_admin') == 'No' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ old('is_super_admin') == 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                        @error('is_super_admin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Active:</label>
                        <select name="is_active" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="Yes" {{ old('is_active') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ old('is_active') == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                        @error('is_active') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
