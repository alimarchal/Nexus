@php
    $ui = \App\Support\Ui::class;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Branch: {{ $branch->code }} - {{ $branch->name }}</h2>
                <p class="mt-1 text-xs text-gray-500">Settings → Branch → Branches</p>
            </div>
            <div class="flex items-center gap-2">
                @can('edit branches')
                    <a href="{{ route('branches.edit', $branch) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Edit</a>
                @endcan
                <a href="{{ route('branches.index') }}" class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800">Back to branches</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
            <x-status-message />

            <section class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg dark:bg-gray-800" aria-labelledby="branch-details">
                <h3 id="branch-details" class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Branch details</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                    <div><dt class="text-gray-500">Branch code</dt><dd class="font-semibold">{{ $branch->code }}</dd></div>
                    <div><dt class="text-gray-500">Branch name</dt><dd class="font-semibold">{{ $branch->name }}</dd></div>
                    <div><dt class="text-gray-500">Region</dt><dd class="font-semibold">{{ $branch->region?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">District</dt><dd class="font-semibold">{{ $branch->district?->name ?? '—' }}</dd></div>
                    <div class="md:col-span-4"><dt class="text-gray-500">Address</dt><dd class="font-semibold">{{ $branch->address ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden bg-white p-6 shadow-xl sm:rounded-lg dark:bg-gray-800" aria-labelledby="branch-users">
                <h3 id="branch-users" class="mb-4 text-base font-bold uppercase tracking-wide text-green-800">Users of this branch ({{ $branch->users->count() }})</h3>
                @if ($branch->users->isEmpty())
                    <p class="text-sm text-gray-500">No user is posted to this branch.</p>
                @else
                    <table class="w-full text-sm">
                        <thead><tr class="bg-gray-100 text-left"><th class="px-2 py-1">Name</th><th class="px-2 py-1">Email</th><th class="px-2 py-1">Role</th></tr></thead>
                        <tbody>
                            @foreach ($branch->users as $user)
                                <tr class="border-b border-gray-100">
                                    <td class="px-2 py-1">
                                        @can('view users')
                                            <a href="{{ route('users.show', $user) }}" class="font-semibold text-blue-800 hover:underline">{{ $user->name }}</a>
                                        @else
                                            {{ $user->name }}
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">{{ $user->email }}</td>
                                    <td class="px-2 py-1">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
