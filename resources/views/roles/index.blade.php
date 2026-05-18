<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings User-Module Roles" :createRoute="route('roles.create')" createLabel="Add Role"
            :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('roles.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="Role Name" />
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

    <x-data-table :items="$roles" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Name', 'align' => 'text-center'],
        ['label' => 'Guard Name', 'align' => 'text-center'],
        ['label' => 'Permissions', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No roles found."
        :emptyRoute="route('roles.create')" emptyLinkText="Add a new role">
        @foreach ($roles as $index => $role)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $roles->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $role->name }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $role->guard_name }}
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex flex-wrap justify-center gap-1">
                        @forelse ($role->permissions->take(3) as $permission)
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-500 dark:text-gray-400">No permissions</span>
                        @endforelse

                        @if ($role->permissions->count() > 3)
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">
                                +{{ $role->permissions->count() - 3 }} more
                            </span>
                        @endif
                    </div>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('roles.edit', $role) }}"
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
