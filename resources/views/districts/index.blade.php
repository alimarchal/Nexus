@php($ui = \App\Support\Ui::class)
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Districts" :createRoute="route('districts.create')" createLabel="Add District" createPermission="create regions"
            :showSearch="true" :showRefresh="true" backRoute="settings.branchsetting" />
    </x-slot>

    <x-filter-section :action="route('districts.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_name" value="District name" />
                <x-input id="filter_name" name="filter[name]" type="text" class="mt-1 block w-full" :value="request('filter.name')" />
            </div>
            <div>
                <x-label for="filter_region_id" value="Region" />
                <select id="filter_region_id" name="filter[region_id]" class="{{ $ui::CONTROL }}">
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) request('filter.region_id') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$districts" variant="grid" caption="Districts by region" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'District', 'align' => 'text-left'],
        ['label' => 'Region', 'align' => 'text-left'],
        ['label' => 'Branches', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No districts found." :emptyRoute="route('districts.create')" emptyLinkText="Add a new district">
        @foreach ($districts as $index => $district)
            <tr>
                <td class="text-center">{{ $districts->firstItem() + $index }}</td>
                <td class="text-left font-semibold">{{ $district->name }}</td>
                <td class="text-left">{{ $district->region->name ?? '—' }}</td>
                <td class="text-center">
                    <a href="{{ route('branches.index', ['filter' => ['district_id' => $district->id]]) }}" class="font-semibold">{{ $district->branches_count }}</a>
                </td>
                <td class="text-center">
                    @can('edit regions')
                        <a href="{{ route('districts.edit', $district) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach
        @if ($districts->total() <= $districts->perPage())
            <x-slot name="footer">
                <tr>
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-center">{{ $districts->sum('branches_count') }}</td>
                    <td></td>
                </tr>
            </x-slot>
        @endif
    </x-data-table>
</x-app-layout>
