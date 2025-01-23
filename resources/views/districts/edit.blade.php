<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit District
        </h2>
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
