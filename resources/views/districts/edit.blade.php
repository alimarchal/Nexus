<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Edit District
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('districts.index') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('districts.update', $district) }}">
                    @csrf
                    @method('PUT')

                    <!-- Region Selection -->
                    <div>
                        <x-label for="region_id" value="Region" />
                        <select name="region_id" id="region_id" class="block mt-1 w-full">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ $district->region_id == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- District Name -->
                    <div class="mt-4">
                        <x-label for="name" value="District Name" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ $district->name }}" required />
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <x-button>Update</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
