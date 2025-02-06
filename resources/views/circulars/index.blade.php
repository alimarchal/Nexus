<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Circulars
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
            <a href="{{ route('circulars.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline-block">Add Circular</span>
            </a>

            <a href="{{ route('product.index') }}"
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
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('circulars.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Division -->
                        <div>
                            <x-label for="division_id" value="{{ __('Division') }}" />
                            <select name="filter[division_id]" id="division_id"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">{{ __('Select Division') }}</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ request('filter.division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Circular Number -->
                        <div>
                            <x-label for="circular_no" value="{{ __('Circular Number') }}" />
                            <x-input type="text" name="filter[circular_no]" id="circular_no"
                                value="{{ request('filter.circular_no') }}" class="block mt-1 w-full" />
                        </div>

                        <!-- Filter by Date Range -->
                        <div>
                            <x-label for="date_from" value="{{ __('Date From') }}" />
                            <x-input type="date" name="filter[date_from]" id="date_from"
                                value="{{ request('filter.date_from') }}" class="block mt-1 w-full" />
                        </div>

                        <div>
                            <x-label for="date_to" value="{{ __('Date To') }}" />
                            <x-input type="date" name="filter[date_to]" id="date_to"
                                value="{{ request('filter.date_to') }}" class="block mt-1 w-full" />
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
                    <!-- Display session message -->
                    <x-status-message />
                    @if ($circulars->count() > 0)
                        <div class="relative overflow-x-auto rounded-lg">
                            <table class="min-w-max w-full table-auto text-sm">
                                <thead>
                                    <tr class="bg-blue-800 text-white uppercase text-sm">
                                        <th class="py-2 px-2 text-center"> # </th>
                                        <th class="py-2 px-2 text-center">Circular No</th>
                                        <th class="py-2 px-2 text-center">Division</th>
                                        <th class="py-2 px-2 text-center">Created At</th>
                                        <th class="py-2 px-2 text-center">Title</th>


                                        <th class="py-2 px-2 text-center">Discription</th>
                                        <th class="py-2 px-2 text-center">Attachment</th>

                                        <th class="py-2 px-2 text-center print:hidden">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-black text-md leading-normal font-extrabold">
                                    @foreach ($circulars->sortByDesc('created_at')->values() as $index => $circular)
                                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                                            <td class="py-1 px-2 text-center">{{ $index + 1 }}</td>
                                            <td class="py-1 px-2 text-center">{{ $circular->circular_no }}</td>
                                            <td class="py-1 px-2 text-center">{{ $circular->division->short_name }}
                                            </td>

                                            <td class="py-1 px-2 text-center">
                                                {{ $circular->created_at->format('d-m-Y') }}</td>
                                            <td class="py-1 px-2 text-center">{{ $circular->title }}</td>

                                            <td class="py-1 px-2 text-center">
                                                <!-- Truncated preview text -->
                                                <span
                                                    class="description-preview block whitespace-pre-wrap break-words">{{ Str::limit($circular->description, 25) }}</span>

                                                <!-- Full description text (hidden initially) -->
                                                <span class="description-full block whitespace-pre-wrap break-words"
                                                    style="display: none;">
                                                    @php
                                                        // Wordwrap the description at 30 characters without cutting words
                                                        $wrappedDescription = wordwrap(
                                                            $circular->description,
                                                            30,
                                                            "\n",
                                                            true,
                                                        );
                                                        // Echo the wrapped description
                                                        echo nl2br(e($wrappedDescription));
                                                    @endphp
                                                </span>

                                                <!-- Link to open the modal with the full description -->
                                                @if (strlen($circular->description) > 30)
                                                    <a href="javascript:void(0);" class="text-blue-600 hover:underline"
                                                        onclick="openModal('{{ addslashes($circular->description) }}')">Read
                                                        more</a>
                                                @endif
                                            </td>

                                            <!-- Modal for full description -->
                                            <div id="descriptionModal"
                                                class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden">
                                                <div class="bg-white p-6 rounded-lg w-11/12 max-w-lg">
                                                    <h2 class="text-xl font-semibold mb-4">Full Description</h2>
                                                    <div id="modalDescription"
                                                        class="whitespace-pre-wrap break-words"></div>
                                                    <button onclick="closeModal()"
                                                        class="mt-4 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Close</button>
                                                </div>
                                            </div>


                                            <td class="py-1 px-2 text-center">
                                                @if ($circular->attachment)
                                                    <a href="{{ Storage::url($circular->attachment) }}"
                                                        class="text-blue-600 hover:underline" target="_blank"
                                                        download>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="w-5 h-5 inline-block">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>


                                            <td class="py-1 px-2 text-center">

                                                <a href="{{ route('circulars.edit', $circular) }}"
                                                    class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-2 py-2">
                            {{ $circulars->links() }}
                        </div>
                    @else
                        <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                            No circulars found.
                            <a href="{{ route('circulars.create') }}" class="text-blue-600 hover:underline">
                                Add a new circular
                            </a>.
                        </p>
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
                // Function to open the modal and show the full description
                function openModal(description) {
                    // Set the description content in the modal
                    document.getElementById('modalDescription').innerText = description;

                    // Show the modal
                    document.getElementById('descriptionModal').classList.remove('hidden');
                }

                // Function to close the modal
                function closeModal() {
                    // Hide the modal
                    document.getElementById('descriptionModal').classList.add('hidden');
                }


                // Prevent clicks inside the filter from closing it
                targetDiv.addEventListener('click', function(event) {
                    event.stopPropagation();
                });

                // Add CSS for smooth transitions
                const style = document.createElement('style');
                style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
                document.head.appendChild(style);
            </script>
            <script>
                function toggleDescription(link) {
                    var preview = link.previousElementSibling.previousElementSibling;
                    var fullDescription = link.previousElementSibling;

                    preview.style.display = 'none';
                    fullDescription.style.display = 'inline';
                    link.style.display = 'none';
                }
            </script>
            <script>
                function toggleDescription(link) {
                    const fullText = link.previousElementSibling; // Get the full description span
                    const previewText = fullText.previousElementSibling; // Get the preview text span

                    // Toggle the visibility of the full text and preview text
                    if (fullText.style.display !== "none") {
                        fullText.style.display = "none"; // Hide full text
                        previewText.style.display = "block"; // Show preview text
                        link.innerText = "Read more"; // Change link text
                    } else {
                        fullText.style.display = "block"; // Show full text
                        previewText.style.display = "none"; // Hide preview text
                        link.innerText = "Read less"; // Change link text
                    }
                }
            </script>
        @endpush
</x-app-layout>
