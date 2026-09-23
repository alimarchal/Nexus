@php
    $ui = \App\Support\Ui::class;
    // Where each built-in role sits in the bank's organogram.
    $roleLevel = [
        'super-admin' => ['System', 'Full access (IT administrators only)'],
        'president-office' => ['President Office', 'Bank-wide view, not tied to an office'],
        'head-office' => ['Head Office', 'Bank-wide access for head-office staff'],
        'division' => ['Division', 'Scoped to one head-office division'],
        'region' => ['Region', 'Scoped to one regional office and its branches'],
        'branch' => ['Branch', 'Scoped to one branch'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Roles" :createRoute="route('roles.create')" createLabel="Add Role" createPermission="create roles"
            :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('roles.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="Role name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full" :value="request('filter.name')" />
            </div>
            <div>
                <x-label for="filter_created_at" value="Created date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full" :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$roles" variant="grid" caption="Roles (Spatie, guard: web)" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'Role', 'align' => 'text-left'],
        ['label' => 'Organogram level', 'align' => 'text-left'],
        ['label' => 'Permissions', 'align' => 'text-center'],
        ['label' => 'Users', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No roles found." :emptyRoute="route('roles.create')" emptyLinkText="Add a new role">
        @foreach ($roles as $index => $role)
            @php
                $level = $roleLevel[$role->name] ?? ['Custom', 'Custom role'];
                $groups = $role->permissions->sortBy('name')
                    ->groupBy(fn ($p) => $ui::permissionModule($p->name))
                    ->map(fn ($items) => $items->pluck('name')->values())->sortKeys();
            @endphp
            <tr>
                <td class="text-center">{{ $roles->firstItem() + $index }}</td>
                <td class="text-left">
                    <div class="font-semibold">{{ \Illuminate\Support\Str::headline($role->name) }}</div>
                    <div class="muted">{{ $role->name }}</div>
                </td>
                <td class="text-left">
                    <div>{{ $level[0] }}</div>
                    <div class="muted">{{ $level[1] }}</div>
                </td>
                <td class="text-center">
                    @if ($role->permissions->isEmpty())
                        <span class="muted">None</span>
                    @else
                        <button type="button" x-data title="View permissions"
                            x-on:click="$dispatch('open-role-permissions-modal', {{ \Illuminate\Support\Js::from([
                                'name' => $role->name,
                                'total' => $role->permissions->count(),
                                'users' => $role->users_count,
                                'groups' => $groups,
                                'editUrl' => route('roles.edit', $role),
                            ]) }})"
                            class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 hover:bg-green-200">
                            {{ $role->permissions->count() }} permissions
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    @endif
                </td>
                <td class="text-center">
                    @can('view users')
                        <a href="{{ route('users.index', ['filter' => ['role' => $role->name]]) }}" class="font-semibold">{{ $role->users_count }}</a>
                    @else
                        {{ $role->users_count }}
                    @endcan
                </td>
                <td class="text-center">
                    <div class="flex justify-center gap-1">
                        @can('edit roles')
                            <a href="{{ route('roles.edit', $role) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                        @endcan
                        @can('delete roles')
                            @if ($role->name !== 'super-admin')
                                <button type="button" x-data class="{{ $ui::ICON_DELETE }}" title="Delete"
                                    x-on:click="$dispatch('open-delete-role-modal', {{ \Illuminate\Support\Js::from(['url' => route('roles.destroy', $role), 'name' => $role->name, 'users' => $role->users_count]) }})">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    {{-- Role permissions modal (same pattern as the "Approve AKSIC" modal) --}}
    <div x-data="{ show: false, info: { name: '', total: 0, users: 0, groups: {}, editUrl: '#' } }"
        x-on:open-role-permissions-modal.window="info = $event.detail; show = true"
        x-on:keydown.escape.window="show = false" x-show="show" x-cloak class="fixed inset-0 z-50" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="show = false"></div>
        <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full overflow-hidden rounded-lg bg-white text-left shadow-xl sm:max-w-2xl" @click.outside="show = false">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                            <svg class="size-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        </div>
                        <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-black">Role permissions &mdash; <span x-text="info.name"></span></h3>
                            <p class="mt-1 text-sm text-gray-700"><span x-text="info.total"></span> permissions &middot; given to <span x-text="info.users"></span> user(s).</p>
                            <div class="mt-3 space-y-3 overflow-y-auto rounded-md border border-gray-400 bg-gray-50 p-3" style="max-height: 22rem;">
                                <template x-for="(items, group) in info.groups" :key="group">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-black" x-text="group"></div>
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
                    @can('edit roles')
                        <a :href="info.editUrl" class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700">Edit role</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <x-alpine-confirmation-modal eventName="open-delete-role-modal" title="Delete Role" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-black">
            Delete role <b x-text="dynamicData.name"></b>? <b x-text="dynamicData.users"></b> user(s) will lose every permission that comes from it.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
