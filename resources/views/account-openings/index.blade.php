<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Account Opening (AOF)" :createRoute="route('account-openings.create')"
            createLabel="New Account Opening" createPermission="create account openings" :showSearch="true"
            :showRefresh="true" backRoute="product.index" />
    </x-slot>

    <x-filter-section :action="route('account-openings.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_request_number" value="Request No" />
                <x-input id="filter_request_number" name="filter[request_number]" type="text"
                    class="mt-1 block w-full" :value="request('filter.request_number')" placeholder="Search by request no..." />
            </div>

            <div>
                <x-label for="filter_customer" value="CIF / Customer" />
                <x-input id="filter_customer" name="filter[customer]" type="text" class="mt-1 block w-full"
                    :value="request('filter.customer')" placeholder="CIF number, name or business..." />
            </div>

            <div>
                <x-label for="filter_aof_form_type" value="Form Type" />
                <select id="filter_aof_form_type" name="filter[aof_form_type]"
                    class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Forms</option>
                    <option value="individual_joint_sole" @selected(request('filter.aof_form_type') === 'individual_joint_sole')>
                        Individual / Joint / Sole Proprietor
                    </option>
                    <option value="entity" @selected(request('filter.aof_form_type') === 'entity')>
                        Government / Company / NGO
                    </option>
                </select>
            </div>

            <div>
                <x-label for="filter_status" value="Status" />
                <select id="filter_status" name="filter[status]"
                    class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach (['draft', 'submitted', 'under_review', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('filter.status') === $status)>
                            {{ \Illuminate\Support\Str::headline($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_branch_id" value="Branch" />
                <select id="filter_branch_id" name="filter[branch_id]"
                    class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('filter.branch_id') === (string) $branch->id)>
                            {{ $branch->code ? $branch->code . ' - ' : '' }}{{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_request_date_from" value="Request Date From" />
                <x-input id="filter_request_date_from" name="filter[request_date_from]" type="date"
                    class="mt-1 block w-full" :value="request('filter.request_date_from')" />
            </div>

            <div>
                <x-label for="filter_request_date_to" value="Request Date To" />
                <x-input id="filter_request_date_to" name="filter[request_date_to]" type="date"
                    class="mt-1 block w-full" :value="request('filter.request_date_to')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$accountOpeningRequests" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Request No', 'align' => 'text-center'],
        ['label' => 'CIF / Customer', 'align' => 'text-left'],
        ['label' => 'Form', 'align' => 'text-center'],
        ['label' => 'Category', 'align' => 'text-center'],
        ['label' => 'Account', 'align' => 'text-center'],
        ['label' => 'Request Date', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No account opening requests found."
        :emptyRoute="route('account-openings.create')" emptyLinkText="Start a new account opening">
        @foreach ($accountOpeningRequests as $index => $accountOpeningRequest)
            @php
                $customer = $accountOpeningRequest->customer;
                $statusColour = match ($accountOpeningRequest->status) {
                    'approved' => 'bg-green-100 text-green-800',
                    'submitted' => 'bg-blue-100 text-blue-800',
                    'under_review' => 'bg-yellow-100 text-yellow-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">{{ $accountOpeningRequests->firstItem() + $index }}</td>

                <td class="px-2 py-1 text-center font-semibold">
                    {{ $accountOpeningRequest->request_number }}
                    <span class="block text-xs font-normal text-gray-500">
                        {{ $accountOpeningRequest->branch?->code }}
                    </span>
                </td>

                <td class="px-2 py-1 text-left">
                    {{ $customer?->displayName() ?? 'Not captured yet' }}
                    <span class="block text-xs font-normal text-gray-500">
                        CIF: {{ $customer?->cif_number ?? '-' }}
                    </span>
                </td>

                <td class="px-2 py-1 text-center text-xs">
                    {{ $accountOpeningRequest->aof_form_type === 'entity' ? 'Entity' : 'Individual' }}
                </td>

                <td class="px-2 py-1 text-center text-xs">
                    {{ $customer?->customerCategory?->name ?? '-' }}
                </td>

                <td class="px-2 py-1 text-center">
                    @if ($accountOpeningRequest->account)
                        {{ $accountOpeningRequest->account->account_number }}
                        <span class="block text-xs font-normal text-gray-500">
                            {{ $accountOpeningRequest->account->product?->code }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>

                <td class="px-2 py-1 text-center">
                    {{ $accountOpeningRequest->request_date?->format('d-m-Y') }}
                </td>

                <td class="px-2 py-1 text-center">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusColour }}">
                        {{ \Illuminate\Support\Str::headline($accountOpeningRequest->status) }}
                    </span>
                </td>

                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('account-openings.show', $accountOpeningRequest) }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-blue-600 transition-colors duration-150 hover:bg-blue-100 hover:text-blue-800"
                            title="View">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>

                        @can('edit account openings')
                            <a href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, 'cif']) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-green-600 transition-colors duration-150 hover:bg-green-100 hover:text-green-800"
                                title="Continue form">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan

                        @can('delete account openings')
                            <button type="button" x-data
                                x-on:click="$dispatch('open-delete-aof-modal', { url: '{{ route('account-openings.destroy', $accountOpeningRequest) }}', number: '{{ $accountOpeningRequest->request_number }}' })"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-red-600 transition-colors duration-150 hover:bg-red-100 hover:text-red-800"
                                title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-aof-modal" title="Delete Account Opening Request"
        confirmButtonText="Delete" confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this account opening request? The captured CIF, account and due
            diligence records linked to it will no longer be reachable from here.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
