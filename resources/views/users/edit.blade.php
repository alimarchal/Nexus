@php
    /**
     * Settings -> Users -> Edit.
     *
     * Same fields and names the UserController@update validates (nothing new
     * is posted): name, email, password, roles[], branch_id / region_id /
     * division_id / head_office_id, is_president_office, permissions[],
     * is_super_admin, is_active. Layout: account, access (roles + unit),
     * permissions grouped by module, then a review modal before saving.
     */
    $roles->loadMissing('permissions:id');

    $selectedRoleIds = array_map('strval', old('roles', $userRoles));
    $selectedPermissionIds = array_map('strval', old('permissions', $userPermissions));

    // Permissions grouped by module: "view aksic claims" -> "Aksic Claims".
    $permissionGroups = $permissions
        ->sortBy('name')
        ->groupBy(fn ($p) => \Illuminate\Support\Str::of($p->name)->after(' ')->title()->value() ?: 'General')
        ->sortKeys();

    $rolePermissionMap = $roles->mapWithKeys(fn ($role) => [
        (string) $role->id => $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->values(),
    ]);
    $permissionNames = $permissions->mapWithKeys(fn ($p) => [(string) $p->id => $p->name]);

    $roleHelp = [
        'super-admin' => 'Full access to everything',
        'head-office' => 'Head office user, bank-wide access',
        'division' => 'Scoped to one division',
        'region' => 'Scoped to one region',
        'branch' => 'Scoped to one branch',
        'president-office' => 'Not tied to any branch or office',
    ];

    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $card = 'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800';
    $cardHead = 'border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-900/40';
    $cardTitle = 'text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400';
    $hint = 'mt-1 text-xs text-gray-500 dark:text-gray-400';
    $isSelf = $user->is(auth()->user());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit User: {{ $user->name }}</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $user->email }}
                    &middot; {{ $user->is_active === 'No' ? 'Inactive' : 'Active' }}
                    @if ($user->roles->isNotEmpty()) &middot; {{ $user->roles->pluck('name')->implode(', ') }} @endif
                </p>
            </div>
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
            <x-status-message />

            @if ($errors->any())
                <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-900/30">
                    <p class="text-sm font-bold text-red-800 dark:text-red-200">Please fix the following before saving:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user->id) }}" id="user-edit-form"
                x-data="userEditForm({
                    roles: {{ \Illuminate\Support\Js::from($selectedRoleIds) }},
                    permissions: {{ \Illuminate\Support\Js::from($selectedPermissionIds) }},
                    roleMap: {{ \Illuminate\Support\Js::from($roles->pluck('name', 'id')) }},
                    rolePermissions: {{ \Illuminate\Support\Js::from($rolePermissionMap) }},
                    permissionNames: {{ \Illuminate\Support\Js::from($permissionNames) }},
                    original: {
                        roles: {{ \Illuminate\Support\Js::from($selectedRoleIds) }},
                        isActive: {{ \Illuminate\Support\Js::from($user->is_active) }},
                        isSuperAdmin: {{ \Illuminate\Support\Js::from($user->is_super_admin) }},
                    },
                })"
                @submit.prevent="openReview()">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                    {{-- 1. Account ------------------------------------------------ --}}
                    <section class="{{ $card }} lg:col-span-2">
                        <header class="{{ $cardHead }}">
                            <h3 class="{{ $cardTitle }}">1. Account</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Login details and whether the user can sign in.</p>
                        </header>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                            <div>
                                <label for="name" class="{{ $lbl }}">Full name <span class="text-red-600">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="{{ $control }}">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="{{ $lbl }}">Email <span class="text-red-600">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $control }}">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div x-data="{ show: false }">
                                <label for="password" class="{{ $lbl }}">New password</label>
                                <div class="relative">
                                    <input id="password" :type="show ? 'text' : 'password'" name="password" minlength="8" autocomplete="new-password"
                                        x-model="password" class="{{ $control }}" style="padding-right: 4rem;" placeholder="Leave blank to keep current">
                                    <button type="button" @click="show = !show" class="absolute px-3 text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300"
                                        style="top: 0.25rem; bottom: 0; right: 0;"
                                        x-text="show ? 'Hide' : 'Show'"></button>
                                </div>
                                <p class="{{ $hint }}">Minimum 8 characters. Leave blank to keep the current password.</p>
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="is_active" class="{{ $lbl }}">Status <span class="text-red-600">*</span></label>
                                    <select id="is_active" name="is_active" x-model="isActive" class="{{ $control }}" @if ($isSelf) title="You cannot deactivate your own account" @endif>
                                        <option value="Yes">Active</option>
                                        <option value="No" @disabled($isSelf)>Inactive</option>
                                    </select>
                                    @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="is_super_admin" class="{{ $lbl }}">Super admin <span class="text-red-600">*</span></label>
                                    <select id="is_super_admin" name="is_super_admin" x-model="isSuperAdmin" class="{{ $control }}">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    @error('is_super_admin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Summary card ------------------------------------------------ --}}
                    <aside class="{{ $card }}">
                        <header class="{{ $cardHead }}">
                            <h3 class="{{ $cardTitle }}">Access summary</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Updates as you change roles and permissions.</p>
                        </header>
                        <dl class="space-y-3 p-5 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="font-semibold" :class="isActive === 'Yes' ? 'text-green-700' : 'text-red-700'" x-text="isActive === 'Yes' ? 'Active' : 'Inactive'"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Super admin</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100" x-text="isSuperAdmin"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Roles</dt>
                                <dd class="text-right font-semibold text-gray-900 dark:text-gray-100" x-text="roleNames().join(', ') || 'None'"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Permissions from roles</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100" x-text="inheritedIds().length"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Extra (individual) permissions</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100" x-text="permissions.length"></dd></div>
                        </dl>
                    </aside>
                </div>

                {{-- 2. Roles & office ------------------------------------------------ --}}
                <section class="{{ $card }} mt-5">
                    <header class="{{ $cardHead }}">
                        <h3 class="{{ $cardTitle }}">2. Roles &amp; office</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Roles decide what the user can do; the office decides which records they see.</p>
                    </header>
                    <div class="space-y-5 p-5">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($roles as $role)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition"
                                    :class="roles.includes('{{ $role->id }}') ? 'border-green-600 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" x-model="roles"
                                        class="mt-0.5 size-4 rounded border-gray-300 text-green-700 focus:ring-green-600">
                                    <span>
                                        <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::headline($role->name) }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                                            {{ $roleHelp[$role->name] ?? 'Custom role' }} &middot; {{ $role->permissions->count() }} permissions
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        {{-- Office fields appear only for the role that needs them --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="!hasRole('president-office')" x-cloak>
                            <div x-show="hasRole('branch')" x-transition>
                                <label for="branch_id" class="{{ $lbl }}">Branch <span class="text-red-600">*</span></label>
                                <select id="branch_id" name="branch_id" class="{{ $control }}">
                                    <option value="">Select branch</option>
                                    @foreach ($branches->sortBy('code') as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $user->branch_id) === (string) $branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="hasRole('region')" x-transition>
                                <label for="region_id" class="{{ $lbl }}">Region <span class="text-red-600">*</span></label>
                                <select id="region_id" name="region_id" class="{{ $control }}">
                                    <option value="">Select region</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" @selected((string) old('region_id', $user->region_id) === (string) $region->id)>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="hasRole('division')" x-transition>
                                <label for="division_id" class="{{ $lbl }}">Division <span class="text-red-600">*</span></label>
                                <select id="division_id" name="division_id" class="{{ $control }}">
                                    <option value="">Select division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}" @selected((string) old('division_id', $user->division_id) === (string) $division->id)>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                                @error('division_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="hasRole('head-office')" x-transition>
                                <label for="head_office_id" class="{{ $lbl }}">Head office <span class="text-red-600">*</span></label>
                                <select id="head_office_id" name="head_office_id" class="{{ $control }}">
                                    <option value="">Select head office</option>
                                    @foreach ($headOffices as $headOffice)
                                        <option value="{{ $headOffice->id }}" @selected((string) old('head_office_id', $user->head_office_id) === (string) $headOffice->id)>{{ $headOffice->name }}</option>
                                    @endforeach
                                </select>
                                @error('head_office_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 md:col-span-2"
                                x-show="!hasRole('branch') && !hasRole('region') && !hasRole('division') && !hasRole('head-office')">
                                Select Branch, Region, Division or Head Office role to link the user to an office.
                            </p>
                        </div>
                        <p class="rounded-md bg-blue-50 px-3 py-2 text-sm text-blue-900 dark:bg-blue-900/30 dark:text-blue-200" x-show="hasRole('president-office')" x-cloak>
                            President Office users are not tied to a branch, region, division or head office.
                        </p>
                        <input type="hidden" name="is_president_office" :value="hasRole('president-office') ? 1 : 0">
                    </div>
                </section>

                {{-- 3. Individual permissions ----------------------------------------- --}}
                <section class="{{ $card }} mt-5">
                    <header class="{{ $cardHead }} flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="{{ $cardTitle }}">3. Extra permissions</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Only for access <b>beyond</b> the selected roles. Items marked
                                <span class="rounded bg-green-100 px-1 font-semibold text-green-800">via role</span> are already granted by a role.
                            </p>
                        </div>
                        <input type="search" x-model="search" placeholder="Search permissions…" aria-label="Search permissions"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </header>
                    <div class="grid max-h-[28rem] grid-cols-1 gap-4 overflow-y-auto p-5 md:grid-cols-2 xl:grid-cols-3" style="max-height: 28rem;">
                        @foreach ($permissionGroups as $group => $items)
                            <fieldset class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                                x-show="groupVisible({{ \Illuminate\Support\Js::from($items->pluck('name')->values()) }})">
                                <legend class="px-1 text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ $group }}</legend>
                                <div class="space-y-1.5">
                                    @foreach ($items as $permission)
                                        <label class="flex items-center justify-between gap-2 text-sm" x-show="matches(@js($permission->name))">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" x-model="permissions"
                                                    class="size-4 rounded border-gray-300 text-blue-700 focus:ring-blue-600">
                                                <span class="text-gray-800 dark:text-gray-200">{{ $permission->name }}</span>
                                            </span>
                                            <span x-show="inheritedIds().includes('{{ $permission->id }}')"
                                                class="shrink-0 rounded bg-green-100 px-1.5 text-[10px] font-semibold text-green-800" style="font-size:10px">via role</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    @error('permissions') <p class="px-5 pb-4 text-sm text-red-600">{{ $message }}</p> @enderror
                </section>

                {{-- Sticky action bar ------------------------------------------------ --}}
                <div class="sticky bottom-0 z-10 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white/95 px-5 py-3 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Fields marked <span class="font-bold text-red-600">*</span> are required. You will review the changes before they are saved.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md bg-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-200">Cancel</a>
                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800">Review &amp; Save</button>
                    </div>
                </div>

                {{-- Review modal (same pattern as the "Approve AKSIC" modal) ------------ --}}
                <div x-show="review" x-cloak class="fixed inset-0 z-50" style="display: none;" x-on:keydown.escape.window="review = false">
                    <div x-show="review" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="review = false"></div>
                    <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
                        <div x-show="review" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                            class="relative w-full overflow-hidden rounded-lg bg-white text-left shadow-xl sm:max-w-lg" @click.outside="review = false">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:size-10"
                                        :class="warnings().length ? 'bg-amber-100' : 'bg-emerald-100'">
                                        <svg class="size-6" :class="warnings().length ? 'text-amber-600' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">Save changes to {{ $user->name }}?</h3>
                                        <div class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Review</div>
                                            <dl class="mt-2 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                                <div><dt class="text-gray-500">Status</dt><dd class="font-semibold text-gray-900" x-text="isActive === 'Yes' ? 'Active' : 'Inactive'"></dd></div>
                                                <div><dt class="text-gray-500">Super admin</dt><dd class="font-semibold text-gray-900" x-text="isSuperAdmin"></dd></div>
                                                <div class="sm:col-span-2"><dt class="text-gray-500">Roles</dt><dd class="font-semibold text-gray-900" x-text="roleNames().join(', ') || 'None'"></dd></div>
                                                <div><dt class="text-gray-500">Extra permissions</dt><dd class="font-semibold text-gray-900" x-text="permissions.length"></dd></div>
                                                <div><dt class="text-gray-500">Password</dt><dd class="font-semibold text-gray-900" x-text="password ? 'Will be changed' : 'Unchanged'"></dd></div>
                                            </dl>
                                        </div>
                                        <ul class="mt-3 space-y-1 text-sm text-amber-800" x-show="warnings().length">
                                            <template x-for="w in warnings()" :key="w"><li class="flex gap-2"><span>&#9888;</span><span x-text="w"></span></li></template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                                <button type="button" @click="review = false"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">Cancel</button>
                                <button type="button" @click="confirmSave()" :disabled="saving"
                                    class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <span x-show="!saving">Save changes</span><span x-show="saving">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('modals')
        <script>
            function userEditForm(config) {
                return {
                    roles: config.roles,
                    permissions: config.permissions,
                    roleMap: config.roleMap,
                    rolePermissions: config.rolePermissions,
                    permissionNames: config.permissionNames,
                    original: config.original,
                    isActive: config.original.isActive || 'Yes',
                    isSuperAdmin: config.original.isSuperAdmin || 'No',
                    password: '',
                    search: '',
                    review: false,
                    saving: false,
                    hasRole(name) {
                        return this.roles.some((id) => this.roleMap[id] === name);
                    },
                    roleNames() {
                        return this.roles.map((id) => this.roleMap[id]).filter(Boolean);
                    },
                    inheritedIds() {
                        const ids = new Set();
                        this.roles.forEach((id) => (this.rolePermissions[id] || []).forEach((p) => ids.add(p)));
                        return [...ids];
                    },
                    matches(name) {
                        const q = this.search.trim().toLowerCase();
                        return q === '' || name.toLowerCase().includes(q);
                    },
                    groupVisible(names) {
                        return names.some((name) => this.matches(name));
                    },
                    warnings() {
                        const list = [];
                        if (this.isActive === 'No' && this.original.isActive !== 'No') list.push('The user will be deactivated and can no longer sign in.');
                        if (this.isSuperAdmin !== this.original.isSuperAdmin) list.push('Super admin will change to ' + this.isSuperAdmin + '.');
                        const before = [...this.original.roles].sort().join(',');
                        if (before !== [...this.roles].sort().join(',')) list.push('Roles will change.');
                        if (this.roles.length === 0) list.push('No role is selected; the user will only have extra permissions.');
                        const needsOffice = { branch: 'branch_id', region: 'region_id', division: 'division_id', 'head-office': 'head_office_id' };
                        Object.entries(needsOffice).forEach(([role, field]) => {
                            const el = document.getElementById(field);
                            if (this.hasRole(role) && el && !el.value) list.push('No ' + role.replace('-', ' ') + ' is selected for the ' + role + ' role.');
                        });
                        return list;
                    },
                    openReview() {
                        const form = document.getElementById('user-edit-form');
                        if (!form.reportValidity()) return;
                        this.saving = false;
                        this.review = true;
                    },
                    confirmSave() {
                        if (this.saving) return;
                        this.saving = true;
                        document.getElementById('user-edit-form').submit();
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
