<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Employee Resources
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
            <a href="{{ route('employee_resources.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Resource</span>
            </a>
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>

            <a href="{{ route('product.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <!-- FILTER SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('employee_resources.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Division -->
                        <div>
                            <x-division />
                        </div>

                        <!-- Filter by Resource No -->
                        <div>
                            <x-input-filters name="resource_no" label="Resource Number" type="text" />
                        </div>

                        <!-- Filter by Title -->
                        <div>
                            <x-input-filters name="title" label="Title" type="text" />
                        </div>

                        <!-- Filter by Date Range -->
                        <div>
                            <x-date-from />
                        </div>
                        <div>
                            <x-date-to />
                        </div>
                    </div>
                    <x-submit-button />
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

            @if ($resources->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="min-w-max w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-2 px-2 text-center">Reference #</th>
                            <th class="py-2 px-2 text-left">Date</th>
                            <th class="py-2 px-2 text-left">Division</th>
                            <th class="py-2 px-2 text-left">Discription</th>
                            {{-- <th class="py-2 px-2 text-left">Category</th> --}}
                            <th class="py-2 px-2 text-left">Title</th>
                            <th class="py-2 px-2 text-center">Attachment</th>
                            <th class="py-2 px-2 text-center print:hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-md leading-normal font-extrabold">
                        @foreach ($resources->sortByDesc('created_at')->values() as $index => $resource)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-1 px-2 text-center">{{ $resource->resource_number }}</td>
                            <td class="py-1 px-2 text-left">{{ $resource->created_at->format('d-m-Y') }}</td>
                            <td class="py-1 px-2 text-left">
                                <abbr title="{{ $resource->division->name ?? '-' }}">
                                    {{ $resource->division->short_name ?? '-' }}
                                </abbr>
                            </td>
                                <td class="py-1 px-2 text-left">
                                {{ $resource->description }}</td>
                            {{-- <td class="py-1 px-2 text-left">
                                {{ $resource->category ?? '-' }}</td> --}}
                            <td class="py-1 px-2 text-left">
                                <div class="w-96 break-words leading-relaxed">
                                    {{ $resource->title }}
                                </div>
                            </td>
                            <td class="py-1 px-2 text-center">
                                @if ($resource->attachment)
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('file.download', $resource->attachment) }}"
                                        class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-200"
                                        title="Download attachment">
                                        Download
                                    </a>
                                    <a href="{{ route('file.view', $resource->attachment) }}"
                                        class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded transition-colors duration-200"
                                        target="_blank" title="View attachment">
                                        View
                                    </a>
                                </div>
                                @else
                                <span class="text-gray-400 text-sm">No file</span>
                                @endif
                            </td>
                            <td class="py-1 px-2 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('employee_resources.edit', $resource) }}"
                                        class="inline-flex items-center px-3 py-1 bg-blue-800 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-2 py-2">
                {{ $resources->links() }}
            </div>
            @else
            <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                No resources found.
                <a href="{{ route('employee_resources.create') }}" class="text-blue-600 hover:underline">
                    Add a new resource
                </a>.
            </p>
            @endif
        </div>
    </div>

    @push('modals')
        {{-- same scripts for toggle filters --}}
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");

            function showFilters() { targetDiv.style.display = 'block'; targetDiv.style.opacity = '0'; targetDiv.style.transform = 'translateY(-20px)'; setTimeout(() => { targetDiv.style.opacity = '1'; targetDiv.style.transform = 'translateY(0)'; }, 10); }
            function hideFilters() { targetDiv.style.opacity = '0'; targetDiv.style.transform = 'translateY(-20px)'; setTimeout(() => { targetDiv.style.display = 'none'; }, 300); }

            btn.onclick = function(event) { event.stopPropagation(); if (targetDiv.style.display === "none") { showFilters(); } else { hideFilters(); } };
            document.addEventListener('click', function(event) { if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn) { hideFilters(); } });
            targetDiv.addEventListener('click', function(event) { event.stopPropagation(); });

            const style = document.createElement('style'); style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`; document.head.appendChild(style);
        </script>
    @endpush
</x-app-layout>
