@php
    $ui = \App\Support\Ui::class;
    $selected = array_map('strval', old('permissions', $rolePermissions ?? []));
    // Group by module: "view aksic claims" -> "Aksic Claims".
    $groups = $permissions->sortBy('name')->groupBy(fn ($p) => $ui::permissionModule($p->name))->sortKeys();
@endphp

<div x-data="{
        selected: @js($selected),
        search: '',
        matches(name) { const q = this.search.trim().toLowerCase(); return q === '' || name.toLowerCase().includes(q); },
        groupIds(ids) { return ids.filter(id => this.selected.includes(id)).length; },
        toggleGroup(ids) {
            const all = ids.every(id => this.selected.includes(id));
            this.selected = all ? this.selected.filter(id => !ids.includes(id)) : [...new Set([...this.selected, ...ids])];
        },
        selectAll(on) { this.selected = on ? @js($permissions->pluck('id')->map(fn ($id) => (string) $id)->values()) : []; }
    }">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="{{ $ui::LABEL }}">Role name <span class="text-red-600">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required maxlength="255"
                data-review="Role" class="{{ $ui::CONTROL }}" placeholder="e.g. region" autocomplete="off">
            <p class="{{ $ui::HINT }}">Lower case with dashes, e.g. <code>branch</code>, <code>region</code>, <code>division</code>, <code>head-office</code>.</p>
            @error('name') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
        </div>
        <div>
            <span class="{{ $ui::LABEL }}">Guard</span>
            <input type="hidden" name="guard_name" value="{{ old('guard_name', $role->guard_name ?? 'web') }}">
            <p class="mt-2 text-sm text-black">{{ old('guard_name', $role->guard_name ?? 'web') }}</p>
            <input type="hidden" data-review="Permissions selected" :value="selected.length">
            @isset($role)
                <input type="hidden" data-review="Users with this role" value="{{ $role->users()->count() }}">
            @endisset
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-lg border border-gray-400">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-400 bg-gray-100 px-4 py-3">
            <div>
                <div class="text-sm font-bold uppercase tracking-wide text-green-800">Permissions</div>
                <div class="text-xs text-gray-700"><b class="text-black" x-text="selected.length"></b> of {{ $permissions->count() }} selected &middot; users with this role get all of them.</div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="search" x-model="search" placeholder="Search permissions…" aria-label="Search permissions"
                    class="w-full rounded-md border-gray-400 text-sm text-black shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64">
                <button type="button" @click="selectAll(true)" class="rounded-md border border-gray-400 bg-white px-3 py-2 text-xs font-semibold text-black hover:bg-gray-50">Select all</button>
                <button type="button" @click="selectAll(false)" class="rounded-md border border-gray-400 bg-white px-3 py-2 text-xs font-semibold text-black hover:bg-gray-50">Clear</button>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 overflow-y-auto p-4 md:grid-cols-2 xl:grid-cols-3" style="max-height: 30rem;">
            @foreach ($groups as $group => $items)
                @php($ids = $items->pluck('id')->map(fn ($id) => (string) $id)->values())
                <fieldset class="rounded-md border border-gray-400 p-3"
                    x-show="{{ \Illuminate\Support\Js::from($items->pluck('name')->values()) }}.some(n => matches(n))">
                    <legend class="flex items-center gap-2 px-1">
                        <span class="text-xs font-bold uppercase tracking-wide text-black">{{ $group }}</span>
                        <span class="rounded bg-gray-200 px-1.5 text-xs font-semibold text-gray-800"><span x-text="groupIds({{ $ids }})"></span>/{{ $ids->count() }}</span>
                        <button type="button" @click="toggleGroup({{ $ids }})" class="text-xs font-semibold text-blue-700 hover:underline">toggle</button>
                    </legend>
                    <div class="space-y-1.5">
                        @foreach ($items as $permission)
                            <label class="flex items-center gap-2 text-sm" x-show="matches(@js($permission->name))">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" x-model="selected"
                                    class="size-4 rounded border-gray-500 text-blue-700 focus:ring-blue-600">
                                <span class="text-black">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
    </div>
    @error('permissions') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
</div>
