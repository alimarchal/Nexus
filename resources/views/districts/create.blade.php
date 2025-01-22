<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add District
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('districts.store') }}">
                    @csrf
                    <div>
                        <x-label for="region_id" value="Region" />
                        <select name="region_id" id="region_id" class="block mt-1 w-full">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <x-label for="name" value="District Name" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-button>Save</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
