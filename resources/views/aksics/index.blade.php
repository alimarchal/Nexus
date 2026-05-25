<x-app-layout>
    <x-slot name="header">
        <x-page-header title="AKSIC" :createRoute="route('aksic.create')" createLabel="" createPermission="create aksics"
            :showSearch="true" :showRefresh="true" backRoute="product.index">
            @can('import aksics')
                <a href="{{ route('aksic.template') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800"
                    title="Download Excel Template">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
                    </svg>
                </a>
                <button type="button" x-data x-on:click="$dispatch('open-import-aksic-modal')"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800"
                    title="Import Excel">
                    Import
                </button>
            @endcan
            <a href="{{ route('reports.aksic-rules-report') }}"
                class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-950 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-green-800"
                title="Report">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
                </svg>
            </a>
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

        <div x-data="{
            show: false,
            actionUrl: '',
            isSubmitting: false,
            selectedSubCategoryId: '',
            categoryId: '',
            loanInfo: {
                category: '-',
                principal: '-',
                kiborRate: '-',
                spreadRate: '-',
                totalRate: '-',
            },
            subCategoriesByParent: {{ Illuminate\Support\Js::from($subCategoriesByParent) }},
            get subCategories() {
                return this.subCategoriesByParent[this.categoryId] || [];
            },
            open(event) {
                this.show = true;
                this.isSubmitting = false;
                this.selectedSubCategoryId = '';
                this.actionUrl = event.detail.url;
                this.categoryId = String(event.detail.categoryId || '');
                this.loanInfo = {
                    category: event.detail.category || '-',
                    principal: event.detail.principal || '-',
                    kiborRate: event.detail.kiborRate || '-',
                    spreadRate: event.detail.spreadRate || '-',
                    totalRate: event.detail.totalRate || '-',
                };
            },
        }" x-on:open-approve-aksic-modal.window="open($event)" x-on:keydown.escape.window="if (show) { show = false }"
            x-show="show" x-cloak class="fixed inset-0 z-50" style="display: none;">
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
                    <form :action="actionUrl" method="POST"
                        @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true;">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                                    <svg class="size-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Approve AKSIC</h3>
                                    <div class="mt-2 space-y-4">
                                        <p class="text-sm text-gray-600">
                                            Select Business Sub Category before approving. This will generate the amortization schedule and save the total interest.
                                        </p>
                                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Loan Information</div>
                                            <dl class="mt-2 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                                <div>
                                                    <dt class="text-gray-500">Business Category</dt>
                                                    <dd class="font-semibold text-gray-900" x-text="loanInfo.category"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-gray-500">Principal</dt>
                                                    <dd class="font-semibold text-gray-900" x-text="loanInfo.principal"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-gray-500">KIBOR</dt>
                                                    <dd class="font-semibold text-gray-900" x-text="loanInfo.kiborRate"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-gray-500">Spread</dt>
                                                    <dd class="font-semibold text-gray-900" x-text="loanInfo.spreadRate"></dd>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <dt class="text-gray-500">Total Rate</dt>
                                                    <dd class="font-semibold text-gray-900" x-text="loanInfo.totalRate"></dd>
                                                </div>
                                            </dl>
                                        </div>
                                        <div>
                                            <x-label for="approve_business_sub_category_id" value="Business Sub Category" :required="true" />
                                            <select id="approve_business_sub_category_id" name="business_sub_category_id"
                                                x-model="selectedSubCategoryId" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">Select Business Sub Category</option>
                                                <template x-for="category in subCategories" :key="category.id">
                                                    <option :value="category.id" x-text="category.name"></option>
                                                </template>
                                            </select>
                                            <p x-show="subCategories.length === 0" class="mt-2 text-xs text-red-600">
                                                No sub category is available for this AKSIC business category.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                            <button type="button" @click="show = false"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting || !selectedSubCategoryId"
                                class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-show="!isSubmitting">Approve</span>
                                <span x-show="isSubmitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endpush
</x-app-layout>
