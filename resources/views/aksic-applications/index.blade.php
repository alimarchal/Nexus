<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            AKSIC Applications 2025
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
            <button id="syncBtn"
                class="inline-flex items-center ml-2 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span id="syncText">Sync API</span>
            </button>
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

    <!-- FILTER SECTION -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters"
            style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('aksic-applications.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Filter by Status -->
                        <div>
                            <label for="filter[status]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="filter[status]" id="filter[status]"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="NotCompleted" {{ request('filter.status')=='NotCompleted' ? 'selected'
                                    : '' }}>Not Completed</option>
                                <option value="Pending" {{ request('filter.status')=='Pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="Forwarded" {{ request('filter.status')=='Forwarded' ? 'selected' : '' }}>
                                    Forwarded</option>
                                <option value="Approved" {{ request('filter.status')=='Approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="Rejected" {{ request('filter.status')=='Rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                            </select>
                        </div>

                        <!-- Filter by Fee Status -->
                        <div>
                            <label for="filter[fee_status]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fee Status</label>
                            <select name="filter[fee_status]" id="filter[fee_status]"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Fee Status</option>
                                <option value="paid" {{ request('filter.fee_status')=='paid' ? 'selected' : '' }}>Paid
                                </option>
                                <option value="unpaid" {{ request('filter.fee_status')=='unpaid' ? 'selected' : '' }}>
                                    Unpaid</option>
                            </select>
                        </div>

                        <!-- Filter by Name -->
                        <div>
                            <x-input-filters name="name" label="Applicant Name" type="text" />
                        </div>

                        <!-- Filter by CNIC -->
                        <div>
                            <x-input-filters name="cnic" label="CNIC" type="text" />
                        </div>

                        <!-- Filter by Application Number -->
                        <div>
                            <x-input-filters name="application_no" label="Application No" type="text" />
                        </div>

                        <!-- Filter by Business Name -->
                        <div>
                            <x-input-filters name="businessName" label="Business Name" type="text" />
                        </div>

                        <!-- Filter by District -->
                        <div>
                            <x-input-filters name="district_name" label="District" type="text" />
                        </div>

                        <!-- Filter by Tehsil -->
                        <div>
                            <x-input-filters name="tehsil_name" label="Tehsil" type="text" />
                        </div>

                        <!-- Filter by Date Range -->
                        <div>
                            <x-date-from />
                        </div>

                        <div>
                            <x-date-to />
                        </div>

                        <!-- Filter by Amount Range -->
                        <div>
                            <label for="filter[amount_min]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Amount</label>
                            <input type="number" step="0.01" name="filter[amount_min]" id="filter[amount_min]"
                                value="{{ request('filter.amount_min') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Min Amount">
                        </div>

                        <div>
                            <label for="filter[amount_max]"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Amount</label>
                            <input type="number" step="0.01" name="filter[amount_max]" id="filter[amount_max]"
                                value="{{ request('filter.amount_max') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Max Amount">
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

            @if ($applications->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="min-w-max w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-white uppercase text-sm">
                            <th class="py-2 px-2 text-center">App ID</th>
                            <th class="py-2 px-2 text-left">Date</th>
                            <th class="py-2 px-2 text-left">Name</th>
                            <th class="py-2 px-2 text-left">CNIC</th>
                            <th class="py-2 px-2 text-left">Application No</th>
                            <th class="py-2 px-2 text-left">Business Name</th>
                            <th class="py-2 px-2 text-center">Status</th>
                            <th class="py-2 px-2 text-center">Fee Status</th>
                            <th class="py-2 px-2 text-right">Amount</th>
                            <th class="py-2 px-2 text-left">District</th>
                            <th class="py-2 px-2 text-center print:hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-md leading-normal font-extrabold">
                        @foreach ($applications->sortByDesc('created_at')->values() as $index => $application)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-1 px-2 text-center">
                                {{ $application->applicant_id }}
                            </td>
                            <td class="py-1 px-2 text-left">
                                {{ $application->created_at->format('d-m-Y') }}
                            </td>
                            <td class="py-1 px-2 text-left">
                                <div class="w-32 break-words leading-relaxed">
                                    {{ $application->name }}
                                </div>
                            </td>
                            <td class="py-1 px-2 text-left">{{ $application->cnic }}</td>
                            <td class="py-1 px-2 text-left">{{ $application->application_no }}</td>
                            <td class="py-1 px-2 text-left">
                                <div class="w-36 break-words leading-relaxed">
                                    {{ $application->businessName ?? '-' }}
                                </div>
                            </td>
                            <td class="py-1 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($application->status == 'Approved') bg-green-100 text-green-800
                                    @elseif($application->status == 'Rejected') bg-red-100 text-red-800
                                    @elseif($application->status == 'Forwarded') bg-blue-100 text-blue-800
                                    @elseif($application->status == 'Pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $application->status }}
                                </span>
                            </td>
                            <td class="py-1 px-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($application->fee_status == 'paid') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($application->fee_status) }}
                                </span>
                            </td>
                            <td class="py-1 px-2 text-right">
                                @if($application->amount)
                                {{ number_format($application->amount, 2) }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="py-1 px-2 text-left">{{ $application->district_name ?? '-' }}</td>
                            <td class="py-1 px-2 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('aksic-applications.show', $application) }}"
                                        class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-200"
                                        title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-2 py-2">
                {{ $applications->links() }}
            </div>
            @else
            <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                No AKSIC applications found.
            </p>
            @endif
        </div>
    </div>

    <!-- Sync Modal -->
    <div id="syncModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">AKSIC API Sync Status</h3>
                    <button id="closeSyncModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Loading State -->
                <div id="syncLoading" class="text-center py-8">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mx-auto"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-gray-600 mt-2">Syncing applications from API...</p>
                </div>

                <!-- Results State -->
                <div id="syncResults" class="hidden">
                    <div class="mb-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <div id="totalCount" class="text-2xl font-bold text-blue-600">0</div>
                                    <div class="text-sm text-gray-600">Total</div>
                                </div>
                                <div>
                                    <div id="createdCount" class="text-2xl font-bold text-green-600">0</div>
                                    <div class="text-sm text-gray-600">Created</div>
                                </div>
                                <div>
                                    <div id="updatedCount" class="text-2xl font-bold text-yellow-600">0</div>
                                    <div class="text-sm text-gray-600">Updated</div>
                                </div>
                                <div>
                                    <div id="failedCount" class="text-2xl font-bold text-red-600">0</div>
                                    <div class="text-sm text-gray-600">Failed</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="successMessage"
                        class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative mb-4 hidden">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline" id="successText"></span>
                    </div>

                    <div id="errorMessage"
                        class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-4 hidden">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline" id="errorText"></span>
                    </div>

                    <div id="errorsList" class="hidden">
                        <h4 class="font-semibold text-gray-900 mb-2">Errors:</h4>
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <ul id="errorsListItems" class="list-disc list-inside text-sm text-red-700"></ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button id="refreshPageBtn"
                        class="hidden bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                        Refresh Page
                    </button>
                    <button id="closeSyncModalBtn"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
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
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);

        // Sync Applications Functionality
        const syncBtn = document.getElementById('syncBtn');
        const syncModal = document.getElementById('syncModal');
        const syncLoading = document.getElementById('syncLoading');
        const syncResults = document.getElementById('syncResults');
        const syncText = document.getElementById('syncText');

        // Modal controls
        document.getElementById('closeSyncModal').addEventListener('click', closeSyncModal);
        document.getElementById('closeSyncModalBtn').addEventListener('click', closeSyncModal);
        document.getElementById('refreshPageBtn').addEventListener('click', () => location.reload());

        // Close modal when clicking outside
        syncModal.addEventListener('click', function(e) {
            if (e.target === syncModal) {
                closeSyncModal();
            }
        });

        function showSyncModal() {
            syncModal.classList.remove('hidden');
            syncLoading.classList.remove('hidden');
            syncResults.classList.add('hidden');
            document.getElementById('successMessage').classList.add('hidden');
            document.getElementById('errorMessage').classList.add('hidden');
            document.getElementById('errorsList').classList.add('hidden');
            document.getElementById('refreshPageBtn').classList.add('hidden');
        }

        function closeSyncModal() {
            syncModal.classList.add('hidden');
            // Reset button state
            syncBtn.disabled = false;
            syncBtn.classList.remove('opacity-50');
            syncText.textContent = 'Sync API';
        }

        function showSyncResults(data) {
            syncLoading.classList.add('hidden');
            syncResults.classList.remove('hidden');
            
            if (data.success) {
                const results = data.results;
                document.getElementById('totalCount').textContent = results.total || 0;
                document.getElementById('createdCount').textContent = results.created || 0;
                document.getElementById('updatedCount').textContent = results.updated || 0;
                document.getElementById('failedCount').textContent = results.failed || 0;
                
                document.getElementById('successMessage').classList.remove('hidden');
                document.getElementById('successText').textContent = data.message;
                
                if (results.errors && results.errors.length > 0) {
                    document.getElementById('errorsList').classList.remove('hidden');
                    const errorsList = document.getElementById('errorsListItems');
                    errorsList.innerHTML = '';
                    results.errors.forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        errorsList.appendChild(li);
                    });
                }
                
                document.getElementById('refreshPageBtn').classList.remove('hidden');
            } else {
                document.getElementById('errorMessage').classList.remove('hidden');
                document.getElementById('errorText').textContent = data.message;
            }
        }

        // Sync button click handler
        syncBtn.addEventListener('click', function() {
            // Disable button and show loading state
            syncBtn.disabled = true;
            syncBtn.classList.add('opacity-50');
            syncText.textContent = 'Syncing...';
            
            // Show modal
            showSyncModal();
            
            // Make API call
            fetch('{{ route('aksic-applications.sync') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(response => response.json())
            .then(data => {
                showSyncResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showSyncResults({
                    success: false,
                    message: 'Network error occurred. Please try again.'
                });
            });
        });
    </script>
    @endpush
</x-app-layout>