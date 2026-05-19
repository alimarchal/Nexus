<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Product AKSIC Rules" :createRoute="route('aksic-rules.create')" createLabel="Add Rule"
            createPermission="create aksic rules" :showSearch="true" :showRefresh="true" backRoute="product.index" />
    </x-slot>

    <x-filter-section :action="route('aksic-rules.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-label for="filter_district_name" value="District" />
                <x-input id="filter_district_name" name="filter[district_name]" type="text" class="mt-1 block w-full"
                    :value="request('filter.district_name')" placeholder="Search district..." />
            </div>
            <div>
                <x-label for="filter_district_id" value="District List" />
                <select id="filter_district_id" name="filter[district_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="">All Districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected((string) request('filter.district_id') === (string) $district->id)>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="filter_is_active" value="Status" />
                <select id="filter_is_active" name="filter[is_active]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="">All</option>
                    <option value="1" @selected(request('filter.is_active') === '1')>Active</option>
                    <option value="0" @selected(request('filter.is_active') === '0')>Inactive</option>
                </select>
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$aksicRules" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'District', 'align' => 'text-left'],
        ['label' => 'Population %', 'align' => 'text-right'],
        ['label' => 'Beneficiaries', 'align' => 'text-right'],
        ['label' => 'Gender Quota', 'align' => 'text-center'],
        ['label' => 'Used', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No AKSIC rules found."
        :emptyRoute="route('aksic-rules.create')" emptyLinkText="Add a new AKSIC rule">
        @foreach ($aksicRules as $index => $rule)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">{{ $aksicRules->firstItem() + $index }}</td>
                <td class="px-2 py-1 text-left font-semibold">{{ $rule->district_name }}</td>
                <td class="px-2 py-1 text-right">{{ number_format((float) $rule->population_percentage, 2) }}%</td>
                <td class="px-2 py-1 text-right">{{ number_format($rule->proposed_beneficiaries) }}</td>
                <td class="px-2 py-1 text-center">
                    M {{ number_format((float) $rule->male_percentage, 0) }}% /
                    F {{ number_format((float) $rule->female_percentage, 0) }}% /
                    SP {{ number_format((float) $rule->special_person_percentage, 0) }}% /
                    T {{ number_format((float) $rule->transgender_percentage, 0) }}%
                </td>
                <td class="px-2 py-1 text-center">{{ $rule->aksics_count }}</td>
                <td class="px-2 py-1 text-center">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('aksic-rules.show', $rule) }}"
                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-md transition-colors duration-150"
                            title="View">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        @can('edit aksic rules')
                            <a href="{{ route('aksic-rules.edit', $rule) }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan
                        @can('delete aksic rules')
                            <button type="button" x-data
                                x-on:click="$dispatch('open-delete-aksic-rule-modal', { url: '{{ route('aksic-rules.destroy', $rule) }}', number: '{{ $rule->district_name }}' })"
                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                                title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="footer">
            <tr>
                <td colspan="2" class="px-2 py-2 text-right">Totals</td>
                <td class="px-2 py-2 text-right">{{ number_format($totals['population_percentage'], 2) }}%</td>
                <td class="px-2 py-2 text-right">{{ number_format($totals['proposed_beneficiaries']) }}</td>
                <td class="px-2 py-2 text-center">100%</td>
                <td class="px-2 py-2 text-center">{{ number_format($totals['used']) }}</td>
                <td colspan="2"></td>
            </tr>
        </x-slot>
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-aksic-rule-modal" title="Delete AKSIC Rule"
        confirmButtonText="Delete" confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this AKSIC rule? This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
