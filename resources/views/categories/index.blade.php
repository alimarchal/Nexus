<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Categories
        </h2>

        <div class="flex justify-center items-center float-right">
            <!-- Search Toggle -->
            <button id="toggle"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800
                       active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Search
            </button>

            <!-- Add New Category -->
            <a href="{{ route('categories.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800
                       active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Category</span>
            </a>

            <!-- Refresh -->
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800
                       active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25
                           8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
             <a href="{{ route('settings.index') }}" class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <!-- FILTER SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('categories.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Filter by Name -->
                        <div>
                            <x-input-filters name="name" label="Category Name" type="text" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <x-submit-button />
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            @if ($categories->count() > 0)
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="min-w-max w-full table-auto text-sm">
                        <thead>
                            <tr class="bg-green-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">#</th>
                                <th class="py-2 px-2 text-left">Category Name</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-black text-md leading-normal font-extrabold">
                            @foreach ($categories as $index => $category)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $index + 1 }}</td>
                                    <td class="py-1 px-2 text-left">{{ $category->name }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('categories.edit', $category->id) }}"
                                                class="inline-flex items-center px-3 py-1 bg-blue-800 text-white rounded-md
                                                       hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
                                                       focus:ring-offset-2">
                                                Edit
                                            </a>
                                            <form action="{{ route('categories.destroy', $category->id) }}"
                                                  method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white
                                                           rounded-md hover:bg-red-500 focus:outline-none focus:ring-2
                                                           focus:ring-red-500 focus:ring-offset-2">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-2 py-2">
                    {{ $categories->links() }}
                </div>
            @else
                <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                    No categories found.
                    <a href="{{ route('categories.create') }}" class="text-blue-600 hover:underline">
                        Add a new category
                    </a>.
                </p>
            @endif
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
        style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
        document.head.appendChild(style);
    </script>
    @endpush
</x-app-layout>
