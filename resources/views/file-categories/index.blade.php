<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings File Categories" :createRoute="route('file-categories.create')"
            createLabel="Add File Category" createPermission="create file categories" :showSearch="true"
            :showRefresh="true" backRoute="settings.index" />
    </x-slot>

    <x-filter-section :action="route('file-categories.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <x-label for="filter_category_name" value="Category Name" />
                <x-input id="filter_category_name" name="filter[category_name]" type="text" class="mt-1 block w-full"
                    :value="request('filter.category_name')" placeholder="Search by name..." />
            </div>

            <div>
                <x-label for="filter_category_code" value="Category Code" />
                <x-input id="filter_category_code" name="filter[category_code]" type="text" class="mt-1 block w-full"
                    :value="request('filter.category_code')" placeholder="Search by code..." />
            </div>

            <div>
                <x-label for="filter_is_active" value="Status" />
                <select id="filter_is_active" name="filter[is_active]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Statuses">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('filter.is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('filter.is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$fileCategories" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Category Code', 'align' => 'text-center'],
        ['label' => 'Category Name', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No file categories found." :emptyRoute="route('file-categories.create')"
        emptyLinkText="Add a new file category">
        @foreach ($fileCategories as $index => $fileCategory)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $fileCategories->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $fileCategory->category_code }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $fileCategory->category_name }}
                </td>
                <td class="px-2 py-1 text-center">
                    <span
                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $fileCategory->is_active === '1' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $fileCategory->is_active === '1' ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        @can('edit file categories')
                            <a href="{{ route('file-categories.edit', $fileCategory) }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan

                        @can('delete file categories')
                            <button type="button" x-data
                                x-on:click="$dispatch('open-delete-file-category-modal', { url: '{{ route('file-categories.destroy', $fileCategory) }}', number: '{{ $fileCategory->category_name }}' })"
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

    <x-alpine-confirmation-modal eventName="open-delete-file-category-modal" title="Delete File Category"
        confirmButtonText="Delete" confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this file category? This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>