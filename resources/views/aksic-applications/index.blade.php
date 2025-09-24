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
                                    <button
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
                                    </button>
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
                event.stopPropagagation();
            });

            // Add CSS for smooth transitions
            const style = document.createElement('style');
            style.textContent = `#filters {transition: opacity 0.3s ease, transform 0.3s ease;}`;
            document.head.appendChild(style);
    </script>
    @endpush
</x-app-layout>