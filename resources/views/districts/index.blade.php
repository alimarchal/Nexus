<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Districts
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>
            <a href="{{ route('districts.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add District</span>
            </a>
            <a href="{{ route('settings.branchsetting') }}"
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
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters">
            <div class="p-6">
                <form method="GET" action="{{ route('districts.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


                        <!-- Filter by District -->
                        <div>
                            <x-label for="district_id" value="{{ __('District') }}" />
                            <select name="filter[name]" id="name"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">{{ __('Select District') }}</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->name }}"
                                        {{ request('filter.name') == $district->name ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <x-button class="mc-bg-blue text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <x-status-message />
                    @if ($districts->count() > 0)
                        <div class="relative overflow-x-auto rounded-lg">
                            <table class="min-w-max w-full table-auto text-sm">
                                <thead>
                                    <tr class="bg-blue-800 text-white uppercase text-sm">
                                        <th class="py-2 px-2 text-center">ID</th>
                                        <th class="py-2 px-2 text-center">Region</th>
                                        <th class="py-2 px-2 text-center">Name</th>
                                        <th class="py-2 px-2 text-center print:hidden">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($districts as $district)
                                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                                            <td class="py-2 px-2 text-center">{{ $district->id }}</td>
                                            <td class="py-2 px-2 text-center">{{ $district->region->name }}</td>
                                            <td class="py-2 px-2 text-center">{{ $district->name }}</td>
                                            <td class="py-2 px-2 text-center">
                                                <a href="{{ route('districts.edit', $district) }}"
                                                    class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700">
                                                    Edit
                                                </a>
                                                {{--  <form method="POST" action="{{ route('districts.destroy', $district) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                    Delete
                                                </button>
                                            </form>  --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-2 py-2">
                            {{ $districts->links() }}
                        </div>
                    @else
                        <p class="text-gray-700 text-center py-4">No districts found.</p>
                    @endif
                </div>
            </div>
        </div>

        @push('modals')
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
                style.textContent = `
            #filters {
                transition: opacity 0.3s ease, transform 0.3s ease;
            }
        `;
                document.head.appendChild(style);
            </script>
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
        @endpush
</x-app-layout>
