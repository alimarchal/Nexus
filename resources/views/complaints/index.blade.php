<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Complaints
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
            <a href="{{ route('complaints.create') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Complaint
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
                <form method="GET" action="{{ route('complaints.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <x-label for="status" value="{{ __('Status') }}" />
                            <select name="filter[status]" id="status"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select Status</option>
                                @foreach ($statusTypes as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('filter.status') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="assigned_to" value="{{ __('Assigned To') }}" />
                            <select name="filter[assigned_to]" id="assigned_to"
                                class="select2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('filter.assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                    <div class="mt-4">
                        <x-button class="mc-bg-blue text-white hover:bg-green-800">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
        <div class="py-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-status-message />
                @if ($complaints->count() > 0)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-blue-800 text-white uppercase text-sm">
                                    <th class="py-2 px-2 text-center">#</th>
                                    <th class="py-2 px-2 text-center">Assigned To</th>
                                    <th class="py-2 px-2 text-center">Subject</th>
                                    <th class="py-2 px-2 text-center">Description</th>
                                    <th class="py-2 px-2 text-center">Created At</th>
                                    <th class="py-2 px-2 text-center">Attachment</th>
                                    <th class="py-2 px-2 text-center">Status</th>
                                    <th class="py-2 px-2 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-black text-md leading-normal font-extrabold">
                                @foreach ($complaints as $index => $complaint)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-1 px-2 text-center">{{ $index + 1 }}</td>
                                        <td class="py-1 px-2 text-center">{{ $complaint->assignedTo->name }}</td>
                                        <td class="py-1 px-2 text-center">{{ $complaint->subject }}</td>
                                        <td class="py-1 px-2 text-center">
                                            <!-- Truncated preview text -->
                                            <span
                                                class="description-preview block whitespace-pre-wrap break-words">{{ Str::limit($complaint->description, 25) }}</span>

                                            <!-- Full description text (hidden initially) -->
                                            <span class="description-full block whitespace-pre-wrap break-words"
                                                style="display: none;">
                                                @php
                                                    // Wordwrap the description at 30 characters without cutting words
                                                    $wrappedDescription = wordwrap(
                                                        $complaint->description,
                                                        30,
                                                        "\n",
                                                        true,
                                                    );
                                                    // Echo the wrapped description
                                                    echo nl2br(e($wrappedDescription));
                                                @endphp
                                            </span>

                                            <!-- Link to open the modal with the full description -->
                                            @if (strlen($complaint->description) > 30)
                                                <a href="javascript:void(0);" class="text-blue-600 hover:underline"
                                                    onclick="openModal('{{ addslashes($complaint->description) }}')">Read
                                                    more</a>
                                            @endif
                                        </td>

                                        <!-- Modal for full description -->
                                        <!-- Modal for full description -->
                                        <div id="descriptionModal"
                                            class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden"
                                            onclick="closeModal(event)">
                                            <div class="bg-white p-6 rounded-lg w-11/12 max-w-lg"
                                                onclick="event.stopPropagation();">
                                                <h2 class="text-xl font-semibold mb-4">Full Description</h2>
                                                <div id="modalDescription" class="whitespace-pre-wrap break-words">
                                                </div>
                                                <button onclick="closeModal()"
                                                    class="mt-4 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">
                                                    Close
                                                </button>
                                            </div>
                                        </div>



                                        <td class="py-1 px-2 text-center">
                                            {{ $complaint->created_at->format('d-m-Y') }}
                                        </td>
                                        <td class="py-1 px-2 text-center">
                                            @if ($complaint->attachments->isNotEmpty())
                                                <a href="{{ Storage::url($complaint->attachments->first()->file_path) }}"
                                                    class="text-blue-600 hover:underline" target="_blank" download>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-5 h-5 inline-block">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                    </svg>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>


                                        <td class="py-1 px-2 text-center">{{ $complaint->status->name }}</td>

                                        <td class="py-1 px-2 text-center flex gap-2 justify-center">
                                            <a href="{{ route('complaints.edit', $complaint) }}"
                                                class="p-2 text-emerald-600 hover:text-white hover:bg-emerald-600 rounded-full transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>

                                            <button type="button"
                                                class="delete-button p-2 text-red-600 hover:text-white hover:bg-red-600 rounded-full transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>

                                            <form class="delete-form" method="POST"
                                                action="{{ route('complaints.destroy', $complaint) }}"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="{{ route('complaints.show', $complaint) }}"
                                                class="p-2 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-2 py-2">
                            {{ $complaints->links() }}
                        </div>
                    @else
                        <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                            No circulars found.
                            <a href="{{ route('complaints.create') }}" class="text-blue-600 hover:underline">
                                Add a new complaint
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
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.querySelectorAll('.delete-button').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();

                        const form = this.nextElementSibling; // Get the next form element

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

            <script>
                function openModal(description) {
                    document.getElementById("modalDescription").innerText = description;
                    document.getElementById("descriptionModal").classList.remove("hidden");
                }

                function closeModal(event) {
                    if (!event || event.target.id === "descriptionModal") {
                        document.getElementById("descriptionModal").classList.add("hidden");
                    }
                }
            </script>
        @endpush
</x-app-layout>
