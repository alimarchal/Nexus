<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            AKSIC
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Search
            </button>
            @can('create aksics')
                <a href="{{ route('aksic.create') }}"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Add AKSIC
                </a>
            @endcan
            <a href="javascript:window.location.reload();"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Refresh
            </a>
            <a href="{{ route('product.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" id="filters" style="display: none">
            <div class="p-6">
                <form method="GET" action="{{ route('aksic.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="filter[status]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <option value="">All Statuses</option>
                                @foreach (['Pending', 'Approved'] as $status)
                                    <option value="{{ $status }}" @selected(request('filter.status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-filters name="name" label="Applicant Name" type="text" />
                        <x-input-filters name="cnic" label="CNIC" type="text" />
                        <x-input-filters name="application_no" label="Application No" type="text" />
                        <x-input-filters name="business_name" label="Business Name" type="text" />
                        <x-input-filters name="district_name" label="District" type="text" />
                        <x-date-from />
                        <x-date-to />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Principal</label>
                            <input type="number" step="0.01" name="filter[amount_min]" value="{{ request('filter.amount_min') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Principal</label>
                            <input type="number" step="0.01" name="filter[amount_max]" value="{{ request('filter.amount_max') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
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
            @if ($aksics->count() > 0)
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="min-w-max w-full table-auto text-sm">
                        <thead>
                            <tr class="bg-green-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">#</th>
                                <th class="py-2 px-2 text-left">Application No</th>
                                <th class="py-2 px-2 text-left">Name</th>
                                <th class="py-2 px-2 text-left">CNIC</th>
                                <th class="py-2 px-2 text-right">Principal</th>
                                <th class="py-2 px-2 text-center">Tenure</th>
                                <th class="py-2 px-2 text-center">Status</th>
                                <th class="py-2 px-2 text-center">Schedule</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-black text-md leading-normal font-extrabold">
                            @foreach ($aksics as $aksic)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->application_no }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->name }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->cnic }}</td>
                                    <td class="py-1 px-2 text-right">{{ number_format((float) $aksic->principal_amount, 2) }}</td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->tenure }}</td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->status }}</td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->amortizations_count }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('aksic.show', $aksic) }}" class="inline-flex items-center px-3 py-1 bg-blue-800 text-white rounded-md hover:bg-blue-700">View</a>
                                            @can('edit aksics')
                                                <a href="{{ route('aksic.edit', $aksic) }}" class="inline-flex items-center px-3 py-1 bg-green-700 text-white rounded-md hover:bg-green-800">Edit</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-2 py-2">{{ $aksics->links() }}</div>
            @else
                <p class="text-gray-700 dark:text-gray-300 text-center py-4">No AKSIC records found.</p>
            @endif
        </div>
    </div>

    @push('modals')
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            btn.onclick = function(event) {
                event.stopPropagation();
                targetDiv.style.display = targetDiv.style.display === "none" ? "block" : "none";
            };
        </script>
    @endpush
</x-app-layout>
