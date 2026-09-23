@php($ui = \App\Support\Ui::class)
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Permissions" :createRoute="route('permissions.create')" createLabel="Add Permission"
            createPermission="create permissions" :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('permissions.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="Permission name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full"
                    :value="request('filter.name')" placeholder="e.g. view aksic" />
            </div>
            <div>
                <x-label for="filter_module" value="Module" />
                <select id="filter_module" name="filter[module]" class="{{ $ui::CONTROL }}">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('filter.module') === $module)>{{ \Illuminate\Support\Str::title($module) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="filter_role_id" value="Used by role" />
                <select id="filter_role_id" name="filter[role_id]" class="{{ $ui::CONTROL }}">
                    <option value="">Any role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) request('filter.role_id') === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="filter_created_at" value="Created date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full"
                    :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$permissions" variant="grid" caption="Permissions (Spatie, guard: web)" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'Permission', 'align' => 'text-left'],
        ['label' => 'Module', 'align' => 'text-left'],
        ['label' => 'Given by roles', 'align' => 'text-left'],
        ['label' => 'Direct users', 'align' => 'text-center'],
        ['label' => 'Created', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No permissions found." :emptyRoute="route('permissions.create')" emptyLinkText="Add a new permission">
        @foreach ($permissions as $index => $permission)
            <tr>
                <td class="text-center">{{ $permissions->firstItem() + $index }}</td>
                <td class="text-left font-semibold">{{ $permission->name }}</td>
                <td class="text-left">{{ $ui::permissionModule($permission->name) }}</td>
                <td class="text-left">
                    <div class="flex flex-wrap gap-1">
                        @forelse ($permission->roles as $role)
                            <span class="{{ $ui::PILL_BLUE }}">{{ $role->name }}</span>
                        @empty
                            <span class="muted">Not in any role</span>
                        @endforelse
                    </div>
                </td>
                <td class="text-center">{{ $permission->users_count ?: '—' }}</td>
                <td class="text-center">{{ optional($permission->created_at)->format('d.m.Y') }}</td>
                <td class="text-center">
                    <div class="flex justify-center gap-1">
                        @can('edit permissions')
                            <a href="{{ route('permissions.edit', $permission) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                        @endcan
                        @can('delete permissions')
                            <button type="button" x-data class="{{ $ui::ICON_DELETE }}" title="Delete"
                                x-on:click="$dispatch('open-delete-permission-modal', {{ \Illuminate\Support\Js::from(['url' => route('permissions.destroy', $permission), 'name' => $permission->name, 'roles' => $permission->roles->count(), 'users' => $permission->users_count]) }})">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-permission-modal" title="Delete Permission" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-black">
            Delete <b x-text="dynamicData.name"></b>? It will be removed from
            <b x-text="dynamicData.roles"></b> role(s) and <b x-text="dynamicData.users || 0"></b> user(s).
            Any screen that checks this permission will stop showing for them.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
