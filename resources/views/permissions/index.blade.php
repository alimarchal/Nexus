<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings User-Module Permissions" :createRoute="route('permissions.create')"
            createLabel="Add Permission" :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('permissions.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="Permission Name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full"
                    :value="request('filter.name')" />
            </div>

            <div>
                <x-label for="filter_created_at" value="Created Date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full"
                    :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$permissions" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Permission Name', 'align' => 'text-center'],
        ['label' => 'Guard Name', 'align' => 'text-center'],
        ['label' => 'Created At', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No permissions found."
        :emptyRoute="route('permissions.create')" emptyLinkText="Add a new permission">
        @foreach ($permissions as $index => $permission)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $permissions->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $permission->name }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $permission->guard_name }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $permission->created_at->format('Y-m-d') }}
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('permissions.edit', $permission) }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-green-600 transition-colors duration-150 hover:bg-green-100 hover:text-green-800"
                            title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-app-layout>
