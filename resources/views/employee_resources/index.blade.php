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

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('employee_resources.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <x-division />
                        </div>
                        <div>
                            <x-input-filters name="resource_no" label="Resource No" type="text" />
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                for="category_id">Category</label>
                            <select name="filter[category_id]" id="category_id"
                                class="select2 mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">All</option>
                                @php($cats = \App\Models\Category::orderBy('name')->get())
                                @foreach($cats as $cat)
                                <option value="{{ $cat->id }}" {{ request('filter.category_id')==$cat->id ? 'selected' :
                                    '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
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

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            @if ($employeeResources->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="min-w-max w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-2 px-2 text-center whitespace-nowrap">Reference #</th>
                            <th class="py-2 px-2 text-left whitespace-nowrap">Date</th>
                            <th class="py-2 px-2 text-left whitespace-nowrap">Division</th>
                            <th class="py-2 px-2 text-left whitespace-nowrap">Resource No</th>
                            <th class="py-2 px-2 text-left whitespace-nowrap">Title</th>
                            <th class="py-2 px-2 text-left whitespace-nowrap">Category</th>
                            <th class="py-2 px-2 text-center whitespace-nowrap">Attachment</th>
                            <th class="py-2 px-2 text-center whitespace-nowrap print:hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-md leading-normal font-extrabold">
                        @foreach ($employeeResources->sortByDesc('created_at')->values() as $resource)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-1 px-2 text-center">{{ $resource->resource_number }}</td>
                            <td class="py-1 px-2 text-left">{{ $resource->created_at->format('d-m-Y') }}</td>
                            <td class="py-1 px-2 text-left">
                                <abbr title="{{ $resource->division->name ?? '-' }}">{{ $resource->division->short_name
                                    ?? '-' }}</abbr>
                            </td>
                            <td class="py-1 px-2 text-left">{{ $resource->resource_no }}</td>
                            <td class="py-1 px-2 text-left">
                                <div class="max-w-xs truncate" title="{{ $resource->title }}">{{ $resource->title }}
                                </div>
                            </td>
                            <td class="py-1 px-2 text-left">{{ $resource->category?->name ?? '-' }}</td>
                            <td class="py-1 px-2 text-center">
                                @if ($resource->attachment)
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('file.download', $resource->attachment) }}" title="Download"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-blue-600 hover:bg-blue-500 text-white rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 3v12m0 0 4-4m-4 4-4-4m8 8H8" />
                                        </svg>
                                        <span class="sr-only">Download</span>
                                    </a>
                                    <a href="{{ route('file.view', $resource->attachment) }}" target="_blank"
                                        title="View"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-gray-600 hover:bg-gray-500 text-white rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12s3-7.5 9.75-7.5S21.75 12 21.75 12s-3 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="3.25" />
                                        </svg>
                                        <span class="sr-only">View</span>
                                    </a>
                                </div>
                                @else
                                <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="py-1 px-2 text-center">
                                <div class="flex justify-center gap-1 flex-wrap max-w-[140px]">
                                    <!-- View -->
                                    <a href="{{ route('employee_resources.show', $resource) }}" title="View"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12c0 0 3-7.5 9.75-7.5S21.75 12 21.75 12s-3 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="3.25" />
                                        </svg>
                                        <span class="sr-only">View</span>
                                    </a>
                                    <!-- Edit -->
                                    <a href="{{ route('employee_resources.edit', $resource) }}" title="Edit"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-blue-700 hover:bg-blue-600 text-white rounded transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487 19.5 7.125m-2.638-2.638-9.9 9.9c-.42.42-.63.63-.81.863a6 6 0 0 0-.57.84c-.17.3-.3.62-.57 1.26l-.742 1.78m12.292-15.643-2.638-2.638m0 0L8.737 8.737m8.125-4.25 2.625 2.625" />
                                        </svg>
                                        <span class="sr-only">Edit</span>
                                    </a>
                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('employee_resources.destroy', $resource) }}"
                                        class="inline" onsubmit="return confirm('Delete this resource?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"
                                            class="inline-flex items-center justify-center w-7 h-7 bg-red-700 hover:bg-red-600 text-white rounded transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 6h18M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m4 0-.805 12.076A2 2 0 0 1 16.203 20H7.797a2 2 0 0 1-1.992-1.924L5 6m4 4v6m6-6v6" />
                                            </svg>
                                            <span class="sr-only">Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-2 py-2">{{ $employeeResources->links() }}</div>
            @else
            <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                No employee resources found.
                <a href="{{ route('employee_resources.create') }}" class="text-blue-600 hover:underline">Add a new
                    resource</a>.
            </p>
            @endif
        </div>
    </div>

    @push('modals')
    <script>
        const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            function showFilters() { targetDiv.style.display = 'block'; targetDiv.style.opacity = '0'; targetDiv.style.transform = 'translateY(-20px)'; setTimeout(() => { targetDiv.style.opacity = '1'; targetDiv.style.transform = 'translateY(0)';}, 10);}
            function hideFilters() { targetDiv.style.opacity = '0'; targetDiv.style.transform = 'translateY(-20px)'; setTimeout(() => { targetDiv.style.display = 'none';}, 300);} btn.onclick = function(event){ event.stopPropagation(); if (targetDiv.style.display === 'none'){ showFilters(); } else { hideFilters(); }}; document.addEventListener('click', function(event){ if (targetDiv.style.display === 'block' && !targetDiv.contains(event.target) && event.target !== btn){ hideFilters(); }}); targetDiv.addEventListener('click', function(event){ event.stopPropagation();}); const style = document.createElement('style'); style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`; document.head.appendChild(style);
    </script>
    <script>
        $(document).ready(function(){ $('#category_id').select2({width:'100%'});});
    </script>
    @endpush
</x-app-layout>