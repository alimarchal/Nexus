<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings User-Module Users" :createRoute="route('users.create')" createLabel=""
            createPermission="create users" :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('users.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-label for="filter_name" value="Name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full"
                    :value="request('filter.name')" placeholder="Search by name..." />
            </div>

            <div>
                <x-label for="filter_email" value="Email" />
                <x-input id="filter_email" name="filter[email]" type="email" class="mt-1 block w-full"
                    :value="request('filter.email')" placeholder="Search by email..." />
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
                <x-label for="filter_role" value="Role" />
                <select id="filter_role" name="filter[role]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Roles">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ request('filter.role') === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_is_active" value="Status" />
                <select id="filter_is_active" name="filter[is_active]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Statuses">
                    <option value="">All Statuses</option>
                    <option value="Yes" {{ request('filter.is_active') === 'Yes' ? 'selected' : '' }}>Active</option>
                    <option value="No" {{ request('filter.is_active') === 'No' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$users" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Name', 'align' => 'text-center'],
        ['label' => 'Email', 'align' => 'text-center'],
        ['label' => 'Branch', 'align' => 'text-center'],
        ['label' => 'Roles', 'align' => 'text-center'],
        ['label' => 'Individual Permissions', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No users found."
        :emptyRoute="route('users.create')" emptyLinkText="Add a new user">
        @foreach ($users as $index => $user)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $users->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $user->name }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $user->email }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $user->branch->name ?? 'N/A' }}
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex flex-wrap justify-center gap-1">
                        @forelse ($user->roles as $role)
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-500 dark:text-gray-400">None</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex flex-wrap justify-center gap-1">
                        @forelse ($user->permissions->take(3) as $permission)
                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-500 dark:text-gray-400">None</span>
                        @endforelse

                        @if ($user->permissions->count() > 3)
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">
                                +{{ $user->permissions->count() - 3 }} more
                            </span>
                        @endif
                    </div>
                </td>
                <td class="px-2 py-1 text-center">
                    <span
                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $user->is_active === 'Yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active === 'Yes' ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        @can('edit users')
                            <a href="{{ route('users.edit', $user) }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan

                        @can('delete users')
                            @if ($user->id !== auth()->id())
                                <button type="button" x-data
                                    x-on:click="$dispatch('open-delete-user-modal', { url: '{{ route('users.destroy', $user) }}', number: '{{ $user->name }}' })"
                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                                    title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-user-modal" title="Delete User" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this user? This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
