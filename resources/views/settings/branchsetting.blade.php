@php
    $ui = \App\Support\Ui::class;
    // Organogram overview: Region -> District -> Branch (small tables, so queried here).
    $regions = \App\Models\Region::query()
        ->withCount(['districts', 'branches', 'users'])
        ->with(['districts' => fn ($q) => $q->withCount('branches')->orderBy('name')])
        ->orderBy('name')->get();
    $cards = [
        ['label' => 'Regions', 'count' => $regions->count(), 'route' => route('regions.index'), 'hint' => 'Regional offices', 'can' => 'view regions',
            'icon' => 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z'],
        ['label' => 'Districts', 'count' => $regions->sum('districts_count'), 'route' => route('districts.index'), 'hint' => 'Grouped under regions', 'can' => 'view regions',
            'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
        ['label' => 'Branches', 'count' => \App\Models\Branch::count(), 'route' => route('branches.index'), 'hint' => 'Service outlets', 'can' => 'view branches',
            'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings — Branch Network" :showSearch="false" :showRefresh="true" backRoute="settings.index" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <x-status-message />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($cards as $card)
                    @can($card['can'])
                        <a href="{{ $card['route'] }}" class="{{ $ui::CARD }} group block transition hover:border-green-700 hover:shadow-lg">
                            <div class="flex items-center justify-between p-5">
                                <div>
                                    <div class="text-3xl font-bold text-black dark:text-gray-100">{{ $card['count'] }}</div>
                                    <div class="mt-1 text-base font-semibold text-black dark:text-gray-100">{{ $card['label'] }}</div>
                                    <div class="text-xs text-gray-700 dark:text-gray-300">{{ $card['hint'] }}</div>
                                </div>
                                <div class="flex size-12 items-center justify-center rounded-full bg-green-100 text-green-800">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                                </div>
                            </div>
                            <div class="border-t border-gray-400 bg-gray-100 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-green-800 group-hover:text-green-900">Open {{ strtolower($card['label']) }} &rarr;</div>
                        </a>
                    @endcan
                @endforeach
            </div>

            {{-- Organogram overview --}}
            @include('aksics._grid-style')
            <section class="{{ $ui::CARD }}">
                <header class="{{ $ui::CARD_HEAD }}">
                    <h3 class="{{ $ui::CARD_TITLE }}">Network overview</h3>
                    <p class="{{ $ui::CARD_SUBTITLE }}">Head Office → Region → District → Branch. Click a number to open the list.</p>
                </header>
                <div class="overflow-x-auto p-3">
                    <table class="aksic-grid aksic-hover">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th>District</th>
                                <th class="text-center">Branches</th>
                                <th class="text-center">Regional office users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($regions as $region)
                                @forelse ($region->districts as $i => $district)
                                    <tr>
                                        @if ($i === 0)
                                            <td rowspan="{{ $region->districts->count() }}" class="font-semibold">
                                                <a href="{{ route('branches.index', ['filter' => ['region_id' => $region->id]]) }}">{{ $region->name }}</a>
                                                <div class="muted">{{ $region->branches_count }} branches</div>
                                            </td>
                                        @endif
                                        <td>{{ $district->name }}</td>
                                        <td class="text-center"><a href="{{ route('branches.index', ['filter' => ['district_id' => $district->id]]) }}">{{ $district->branches_count }}</a></td>
                                        @if ($i === 0)
                                            <td rowspan="{{ $region->districts->count() }}" class="text-center">{{ $region->users_count ?: '—' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="font-semibold">{{ $region->name }}</td>
                                        <td class="muted">No districts</td>
                                        <td class="text-center">{{ $region->branches_count }}</td>
                                        <td class="text-center">{{ $region->users_count ?: '—' }}</td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr><td colspan="4" class="text-center">No regions set up yet.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right">Total</td>
                                <td class="text-center">{{ $regions->sum('branches_count') }}</td>
                                <td class="text-center">{{ $regions->sum('users_count') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
