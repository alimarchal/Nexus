<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Product File Management" :createRoute="route('file-management-systems.create')"
            createLabel="Add Document" createPermission="create file management systems" :showSearch="true"
            :showRefresh="true" backRoute="product.index" />
    </x-slot>

    <x-filter-section :action="route('file-management-systems.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-label for="filter_digital_id" value="Digital ID" />
                <x-input id="filter_digital_id" name="filter[digital_id]" type="text" class="mt-1 block w-full"
                    :value="request('filter.digital_id')" placeholder="Search by digital id..." />
            </div>

            <div>
                <x-label for="filter_file_no" value="File No" />
                <x-input id="filter_file_no" name="filter[file_no]" type="text" class="mt-1 block w-full"
                    :value="request('filter.file_no')" placeholder="Search by file no..." />
            </div>

            <div>
                <x-label for="filter_title" value="Title" />
                <x-input id="filter_title" name="filter[title]" type="text" class="mt-1 block w-full"
                    :value="request('filter.title')" placeholder="Search by title..." />
            </div>

            <div>
                <x-label for="filter_file_category_id" value="Category" />
                <select id="filter_file_category_id" name="filter[file_category_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Categories">
                    <option value="">All Categories</option>
                    @foreach ($fileCategories as $fileCategory)
                        <option value="{{ $fileCategory->id }}" {{ request('filter.file_category_id') === $fileCategory->id ? 'selected' : '' }}>
                            {{ $fileCategory->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_branch_id" value="Branch" />
                <select id="filter_branch_id" name="filter[branch_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Branches">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) request('filter.branch_id') === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->code ? $branch->code . ' - ' : '' }}{{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_region_id" value="Region" />
                <select id="filter_region_id" name="filter[region_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Regions">
                    <option value="">All Regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" {{ (string) request('filter.region_id') === (string) $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_division_id" value="Division" />
                <select id="filter_division_id" name="filter[division_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Divisions">
                    <option value="">All Divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" {{ (string) request('filter.division_id') === (string) $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_document_date_from" value="Document Date From" />
                <x-input id="filter_document_date_from" name="filter[document_date_from]" type="date"
                    class="mt-1 block w-full" :value="request('filter.document_date_from')" />
            </div>

            <div>
                <x-label for="filter_document_date_to" value="Document Date To" />
                <x-input id="filter_document_date_to" name="filter[document_date_to]" type="date"
                    class="mt-1 block w-full" :value="request('filter.document_date_to')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$fileManagementSystems" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Digital ID', 'align' => 'text-center'],
        ['label' => 'Category', 'align' => 'text-center'],
        ['label' => 'Stored In', 'align' => 'text-center'],
        ['label' => 'Created By', 'align' => 'text-center'],
        ['label' => 'Document Date', 'align' => 'text-center'],
        ['label' => 'Title', 'align' => 'text-center'],
        ['label' => 'Pages', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No document records found."
        :emptyRoute="route('file-management-systems.create')" emptyLinkText="Add a new document record">
        @foreach ($fileManagementSystems as $index => $fileManagementSystem)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystems->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $fileManagementSystem->digital_id }}
                    @if ($fileManagementSystem->file_no)
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                            {{ $fileManagementSystem->file_no }}
                        </span>
                    @endif
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystem->fileCategory->category_name ?? 'N/A' }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystem->fileable_label }}:
                    {{ $fileManagementSystem->fileable_type === 'division' ? ($fileManagementSystem->fileable?->short_name ?? $fileManagementSystem->fileable_name ?? 'N/A') : ($fileManagementSystem->fileable_name ?? 'N/A') }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystem->creator?->name ?? 'System' }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystem->document_date?->format('d-m-Y') }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileManagementSystem->title ?? '-' }}
                </td>
                <td class="px-2 py-1 text-center">
                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                        {{ $fileManagementSystem->media->count() }}
                    </span>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('file-management-systems.show', $fileManagementSystem) }}"
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

                        @can('edit file management systems')
                            <a href="{{ route('file-management-systems.edit', $fileManagementSystem) }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan

                        @can('delete file management systems')
                            <button type="button" x-data
                                x-on:click="$dispatch('open-delete-fms-modal', { url: '{{ route('file-management-systems.destroy', $fileManagementSystem) }}', number: '{{ $fileManagementSystem->digital_id }}' })"
                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                                title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-fms-modal" title="Delete Document Record"
        confirmButtonText="Delete" confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this document record? All uploaded pages will be removed as well.
            This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>