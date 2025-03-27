<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Documents
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
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
            <a href="{{ route('docs.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Document</span>
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('docs.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Title Filter -->
                        <div>
                            <x-label for="title" value="{{ __('Document Title') }}" />
                            <input type="text" name="filter[title]" id="title"
                                value="{{ request('filter.title') }}"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <x-label for="category_id" value="{{ __('Category') }}" />
                            <select name="filter[category_id]" id="category_id"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('filter.category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Division Filter -->
                        <div>
                            <x-label for="division_id" value="{{ __('Division') }}" />
                            <select name="filter[division_id]" id="division_id"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">All Divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ request('filter.division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Created Date Filter -->
                        <div>
                            <x-label for="created_at" value="{{ __('Created Date') }}" />
                            <input type="date" name="filter[created_at]" id="created_at"
                                value="{{ request('filter.created_at') }}"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button class="bg-blue-800 text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-status-message />
                @if ($docs->count() > 0)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-blue-800 text-white uppercase text-sm">
                                    <th class="py-2 px-2 text-center">ID</th>
                                    <th class="py-2 px-2 text-center">Title</th>
                                    <th class="py-2 px-2 text-center">Category</th>
                                    <th class="py-2 px-2 text-center">Division</th>
                                    <th class="py-2 px-2 text-center">Documents</th>

                                    <th class="py-2 px-2 text-center">Created At</th>
                                    <th class="py-2 px-2 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-800 text-md leading-normal font-semibold">
                                @foreach ($docs as $doc)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-2 px-2 text-center">{{ $doc->id }}</td>
                                        <td class="py-2 px-2 text-center">{{ $doc->title }}</td>
                                        <td class="py-2 px-2 text-center">{{ $doc->category->name }}</td>
                                        <td class="py-2 px-2 text-center">{{ $doc->division->name }}</td>
                                        <td class="py-1 px-2 text-center">
                                            @if ($doc->document && Storage::exists($doc->document))
                                                <a href="{{ asset('storage/' . $doc->document) }}"
                                                    class="text-blue-600 hover:underline" target="_blank" download>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-5 h-5 inline-block">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="text-red-500">-</span>
                                            @endif
                                        </td>



                                        <td class="py-2 px-2 text-center">{{ $doc->created_at->format('Y-m-d') }}</td>
                                        <td class="py-2 px-2 text-center flex justify-center space-x-2">
                                            <a href="{{ route('docs.edit', $doc) }}"
                                                class="px-4 py-2 text-white bg-green-800 hover:bg-green-700 rounded-md">Edit</a>
                                            <form action="{{ route('docs.destroy', $doc) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="delete-button px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-md">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4 px-2 py-2">
                            {{ $docs->links() }}
                        </div>
                    </div>
                @else
                    <p class="text-center py-6">No documents found. <a href="{{ route('docs.create') }}"
                            class="text-blue-600 hover:underline">Add a new document</a>.</p>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
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
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            // Toggle Filters
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            btn.onclick = function(event) {
                event.stopPropagation();
                targetDiv.style.display = targetDiv.style.display === "none" ? "block" : "none";
            };
            document.addEventListener('click', function(event) {
                if (!targetDiv.contains(event.target) && event.target !== btn) {
                    targetDiv.style.display = "none";
                }
            });
        </script>
    @endpush
</x-app-layout>
