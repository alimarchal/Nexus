@php($ui = \App\Support\Ui::class)
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Regions" :createRoute="route('regions.create')" createLabel="Add Region" createPermission="create regions"
            :showSearch="true" :showRefresh="true" backRoute="settings.branchsetting" />
    </x-slot>

    <x-filter-section :action="route('regions.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="Region name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full" :value="request('filter.name')" />
            </div>
            <div>
                <x-label for="filter_created_at" value="Created date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full" :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$regions" variant="grid" caption="Regional offices" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'Region', 'align' => 'text-left'],
        ['label' => 'Districts', 'align' => 'text-center'],
        ['label' => 'Branches', 'align' => 'text-center'],
        ['label' => 'Regional office users', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No regions found." :emptyRoute="route('regions.create')" emptyLinkText="Add a new region">
        @foreach ($regions as $index => $region)
            <tr>
                <td class="text-center">{{ $regions->firstItem() + $index }}</td>
                <td class="text-left font-semibold">{{ $region->name }}</td>
                <td class="text-center"><a href="{{ route('districts.index', ['filter' => ['region_id' => $region->id]]) }}" class="font-semibold">{{ $region->districts_count }}</a></td>
                <td class="text-center"><a href="{{ route('branches.index', ['filter' => ['region_id' => $region->id]]) }}" class="font-semibold">{{ $region->branches_count }}</a></td>
                <td class="text-center">{{ $region->users_count ?: '—' }}</td>
                <td class="text-center">
                    @can('edit regions')
                        <a href="{{ route('regions.edit', $region) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach
        @if ($regions->total() <= $regions->perPage())
            <x-slot name="footer">
                <tr>
                    <td colspan="2" class="text-right">Total</td>
                    <td class="text-center">{{ $regions->sum('districts_count') }}</td>
                    <td class="text-center">{{ $regions->sum('branches_count') }}</td>
                    <td class="text-center">{{ $regions->sum('users_count') }}</td>
                    <td></td>
                </tr>
            </x-slot>
        @endif
    </x-data-table>
</x-app-layout>
