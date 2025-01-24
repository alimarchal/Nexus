<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Region') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('regions.update', $region) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-label for="name" value="Region Name" />
                            <x-input id="name" name="name" class="block mt-1 w-full" type="text" value="{{ $region->name }}" required autofocus />
                            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <x-button class="bg-blue-500 text-white">Update</x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
