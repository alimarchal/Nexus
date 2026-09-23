<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Users" :createRoute="route('users.create')" createLabel="Add User"
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

    <x-data-table :items="$users" variant="grid" caption="Users" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Name / Email', 'align' => 'text-left'],
        ['label' => 'Office', 'align' => 'text-left'],
        ['label' => 'Roles', 'align' => 'text-center'],
        ['label' => 'Extra Permissions', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No users found."
        :emptyRoute="route('users.create')" emptyLinkText="Add a new user">
        @foreach ($users as $index => $user)
            @php
                // Office the user is scoped to (branch, region, division or head office).
                $office = match (true) {
                    (bool) $user->branch => ['Branch', trim(($user->branch->code ? $user->branch->code.' - ' : '').$user->branch->name)],
                    (bool) $user->region => ['Region', $user->region->name],
                    (bool) $user->division => ['Division', $user->division->name],
                    (bool) $user->headOffice => ['Head Office', $user->headOffice->name],
                    (bool) $user->is_president_office => ['President Office', 'All offices'],
                    default => [null, null],
                };
                // Extra permissions grouped by module for the details modal.
                $permissionGroups = $user->permissions->sortBy('name')
                    ->groupBy(fn ($p) => \Illuminate\Support\Str::of($p->name)->after(' ')->title()->value())
                    ->map(fn ($items) => $items->pluck('name')->values())
                    ->sortKeys();
            @endphp
            <tr class="border-b border-gray-200 text-sm text-black hover:bg-gray-100 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-700">
                <td class="px-2 py-1.5 text-center">{{ $users->firstItem() + $index }}</td>
                <td class="px-2 py-1.5 text-left">
                    <div class="font-semibold">{{ $user->name }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $user->email }}</div>
                </td>
                <td class="px-2 py-1.5 text-left">
                    @if ($office[0])
                        <div>{{ $office[1] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ $office[0] }}</div>
                    @else
                        <span class="text-xs text-gray-500">&mdash;</span>
                    @endif
                </td>
                <td class="px-2 py-1.5 text-center">
                    <div class="flex flex-wrap justify-center gap-1">
                        @forelse ($user->roles as $role)
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">{{ $role->name }}</span>
                        @empty
                            <span class="text-xs text-gray-500">None</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-2 py-1.5 text-center">
                    @if ($user->permissions->isEmpty())
                        <span class="text-xs text-gray-500">None</span>
                    @else
                        {{-- Compact count; the full list opens in a modal instead of widening the table --}}
                        <button type="button" x-data
                            x-on:click="$dispatch('open-user-permissions-modal', {{ \Illuminate\Support\Js::from([
                                'name' => $user->name,
                                'total' => $user->permissions->count(),
                                'groups' => $permissionGroups,
                                'editUrl' => route('users.edit', $user),
                            ]) }})"
                            class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 hover:bg-green-200"
                            title="View extra permissions">
                            {{ $user->permissions->count() }} {{ \Illuminate\Support\Str::plural('permission', $user->permissions->count()) }}
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    @endif
                </td>
                <td class="px-2 py-1.5 text-center">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->is_active === 'Yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active === 'Yes' ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-2 py-1.5 text-center">
                    <div class="flex justify-center space-x-2">
                        @can('edit users')
                            <a href="{{ route('users.edit', $user) }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan
                        @can('delete users')
                            @if ($user->id !== auth()->id())
                                <button type="button" x-data
                                    x-on:click="$dispatch('open-delete-user-modal', { url: '{{ route('users.destroy', $user) }}', number: '{{ $user->name }}' })"
                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                                    title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    {{-- Extra-permissions modal (same pattern as the "Approve AKSIC" modal) --}}
    <div x-data="{ show: false, info: { name: '', total: 0, groups: {}, editUrl: '#' } }"
        x-on:open-user-permissions-modal.window="info = $event.detail; show = true"
        x-on:keydown.escape.window="show = false" x-show="show" x-cloak class="fixed inset-0 z-50" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="show = false"></div>
        <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full overflow-hidden rounded-lg bg-white text-left shadow-xl sm:max-w-lg" @click.outside="show = false">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                            <svg class="size-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        </div>
                        <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Extra permissions &mdash; <span x-text="info.name"></span></h3>
                            <p class="mt-1 text-sm text-gray-600">Given in addition to the user's roles (<span x-text="info.total"></span> total).</p>
                            <div class="mt-3 max-h-80 space-y-3 overflow-y-auto rounded-md border border-gray-200 bg-gray-50 p-3" style="max-height: 20rem;">
                                <template x-for="(items, group) in info.groups" :key="group">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="group"></div>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <template x-for="p in items" :key="p">
                                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800" x-text="p"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                    <button type="button" @click="show = false" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">Close</button>
                    @can('edit users')
                        <a :href="info.editUrl" class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700">Edit user</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <x-alpine-confirmation-modal eventName="open-delete-user-modal" title="Delete User" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this user? This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
