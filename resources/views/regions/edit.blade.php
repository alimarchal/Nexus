<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            {{ __('Edit Region') }}
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('regions.index') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
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
