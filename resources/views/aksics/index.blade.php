<x-app-layout>
    <x-slot name="header">
        <x-page-header title="AKSIC" :createRoute="route('aksic.create')" createLabel="" createPermission="create aksics"
            :showSearch="true" :showRefresh="true" backRoute="product.index">
            {{-- All header buttons share one height (size-6 icon + py-2, same as the
                 built-in +, filter, refresh, back and print buttons); module buttons
                 carry a short label next to their icon. --}}
            @can('import aksics')
                <a href="{{ route('aksic.template') }}" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800" title="Download Excel Template">
                    <svg class="size-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    <span class="hidden md:inline-block">Template</span>
                </a>
                <button type="button" x-data x-on:click="$dispatch('open-import-aksic-modal')" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800" title="Import Excel">
                    <svg class="size-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    <span class="hidden md:inline-block">Import</span>
                </button>
            @endcan
            <a href="{{ route('reports.aksic-rules-report') }}" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800" title="AKSIC Rules &amp; Loans Report">
                <svg class="size-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                <span class="hidden md:inline-block">Report</span>
            </a>
            @can('view aksic budget')
                <a href="{{ route('aksic-budgets.index') }}" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800" title="Markup Budget">
                    <svg class="size-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    <span class="hidden md:inline-block">Budget</span>
                </a>
            @endcan
            @can('view aksic claims')
                <a href="{{ route('aksic-claims.index') }}" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800" title="Claims">
                    <svg class="size-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                    <span class="hidden md:inline-block">Claims</span>
                </a>
            @endcan
        </x-page-header>
    </x-slot>

    <x-filter-section :action="route('aksic.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-label for="filter_status" value="Status" />
                <select id="filter_status" name="filter[status]"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">All Statuses</option>
                    @foreach (['Pending', 'Approved', 'Reject'] as $status)
                        <option value="{{ $status }}" @selected(request('filter.status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <x-input-filters name="name" label="Applicant Name" type="text" />
            <x-input-filters name="cnic" label="CNIC" type="text" />
            <x-input-filters name="application_no" label="Application No" type="text" />
            <x-input-filters name="business_name" label="Business Name" type="text" />
            <x-input-filters name="district_name" label="District" type="text" />

            <div>
                <x-label for="filter_quota" value="Gender Quota" />
                <select id="filter_quota" name="filter[quota]"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">All Quotas</option>
                    @foreach (['Male', 'Female', 'Disabled', 'Special Person', 'Transgender'] as $quota)
                        <option value="{{ $quota }}" @selected(request('filter.quota') === $quota)>{{ $quota }}</option>
                    @endforeach
                </select>
            </div>

            <x-date-from />
            <x-date-to />

            <div>
                <x-label for="filter_amount_min" value="Min Principal" />
                <x-input id="filter_amount_min" name="filter[amount_min]" type="number" step="0.01" class="mt-1 block w-full"
                    :value="request('filter.amount_min')" />
            </div>

            <div>
                <x-label for="filter_amount_max" value="Max Principal" />
                <x-input id="filter_amount_max" name="filter[amount_max]" type="number" step="0.01" class="mt-1 block w-full"
                    :value="request('filter.amount_max')" />
            </div>
        </div>
    </x-filter-section>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2 pb-16">
        <x-status-message />
        @if ($errors->any())
            <div class="mb-3 rounded-md bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('import_errors'))
            <div class="mb-3 rounded-md bg-amber-50 p-4 text-sm text-amber-800">
                <div class="font-semibold">Import skipped rows</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach (array_slice(session('import_errors'), 0, 10) as $importError)
                        <li>{{ $importError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            @if ($aksics->count() > 0)
                <div class="relative overflow-x-auto rounded-lg">
                    <table class="min-w-max w-full table-auto text-sm">
                        <thead>
                            <tr class="bg-green-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">#</th>
                                <th class="py-2 px-2 text-left">Name</th>
                                <th class="py-2 px-2 text-left">CNIC</th>
                                <th class="py-2 px-2 text-left">District</th>
                                <th class="py-2 px-2 text-center">Quota</th>
                                <th class="py-2 px-2 text-center">Gender</th>
                                <th class="py-2 px-2 text-right">Principal</th>
                                <th class="py-2 px-2 text-right">Total Interest</th>
                                <th class="py-2 px-2 text-center">Tenure</th>
                                <th class="py-2 px-2 text-center">Status</th>
                                <th class="py-2 px-2 text-center">Schedule</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-black text-md leading-normal font-extrabold">
                            @foreach ($aksics as $aksic)
                                @php
                                    $canModifyAksic = $aksic->amortizations_count === 0 || auth()->user()?->hasRole('super-admin');
                                @endphp
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->name }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->cnic }}</td>
                                    <td class="py-1 px-2 text-left">{{ $aksic->district_name ?? $aksic->district?->name ?? '-' }}</td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->quota ?? '-' }}</td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->gender ?? '-' }}</td>
                                    <td class="py-1 px-2 text-right">{{ number_format((float) $aksic->principal_amount, 2) }}</td>
                                    <td class="py-1 px-2 text-right">
                                        {{ $aksic->total_interest === null ? '-' : number_format((float) $aksic->total_interest, 2) }}
                                    </td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->tenure }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $aksic->status === 'Reject' ? 'bg-red-100 text-red-800' : ($aksic->amortizations_count > 0 ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $aksic->status === 'Reject' ? 'Reject' : ($aksic->amortizations_count > 0 ? 'Generated' : 'Pending') }}
                                        </span>
                                    </td>
                                    <td class="py-1 px-2 text-center">{{ $aksic->amortizations_count }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('aksic.show', $aksic) }}"
                                                class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-md transition-colors duration-150"
                                                title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>
                                            @if ($canModifyAksic && $aksic->status !== 'Reject')
                                                @can('edit aksics')
                                                    <a href="{{ route('aksic.edit', $aksic) }}"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                                        title="Edit">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" />
                                                        </svg>
                                                    </a>
                                                @endcan
                                                @can('approve aksics')
                                                    <button type="button" x-data
                                                        data-approve-url="{{ route('aksic.approve', $aksic) }}"
                                                        x-on:click="$dispatch('open-approve-aksic-modal', {{ Illuminate\Support\Js::from([
                                                            'url' => route('aksic.approve', $aksic),
                                                            'categoryId' => (string) $aksic->business_category_id,
                                                            'category' => $aksic->businessCategory?->name ?? '-',
                                                            'principal' => $aksic->principal_amount === null ? '-' : number_format((float) $aksic->principal_amount, 2),
                                                            'kiborRate' => $aksic->kibor_rate === null ? '-' : number_format((float) $aksic->kibor_rate, 2).'%',
                                                            'spreadRate' => $aksic->spread_rate === null ? '-' : number_format((float) $aksic->spread_rate, 2).'%',
                                                            'totalRate' => $aksic->total_rate === null ? '-' : number_format((float) $aksic->total_rate, 2).'%',
                                                        ]) }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                                                        title="Pending - Generate Schedule">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                @endcan
                                            @endif
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
        <div x-data="{ show: false, isSubmitting: false }" x-on:open-import-aksic-modal.window="show = true; isSubmitting = false"
            x-on:keydown.escape.window="if (show) { show = false }" x-show="show" x-cloak class="fixed inset-0 z-50"
            style="display: none;">
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 backdrop-blur-none"
                x-transition:enter-end="opacity-100 backdrop-blur-sm"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 backdrop-blur-sm"
                x-transition:leave-end="opacity-0 backdrop-blur-none"
                class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-all" @click="show = false">
            </div>

            <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
                <div x-show="show" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    @click.outside="show = false">
                    <form method="POST" action="{{ route('aksic.import') }}" enctype="multipart/form-data"
                        @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true;">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div>
                                <div class="w-full text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Import AKSIC Excel</h3>
                                    <div class="mt-4 max-w-sm">
                                        <x-label for="aksic_import_file" value="Excel File" :required="true" />
                                        <input id="aksic_import_file" type="file" name="file" accept=".xlsx" required
                                            class="mt-1 block w-full rounded-md border border-gray-300 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                            <button type="button" @click="show = false"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-green-950 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-show="!isSubmitting">Import</span>
                                <span x-show="isSubmitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('aksics._approve-modal')
    @endpush
</x-app-layout>
