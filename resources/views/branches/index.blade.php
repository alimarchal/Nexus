@php($ui = \App\Support\Ui::class)
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Branches" :createRoute="route('branches.create')" createLabel="Add Branch" createPermission="create branches"
            :showSearch="true" :showRefresh="true" backRoute="settings.branchsetting" />
    </x-slot>

    <x-filter-section :action="route('branches.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_search" value="Code / name / address" />
                <x-input id="filter_search" name="filter[search]" type="text" class="mt-1 block w-full" :value="request('filter.search')" placeholder="e.g. 0087 or Chechian" />
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
            <div>
                <x-label for="filter_district_id" value="District" />
                <select id="filter_district_id" name="filter[district_id]" class="{{ $ui::CONTROL }}">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected((string) request('filter.district_id') === (string) $district->id)>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$branches" variant="grid" caption="Region → District → Branch" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'Code', 'align' => 'text-center', 'width' => '5rem'],
        ['label' => 'Branch', 'align' => 'text-left'],
        ['label' => 'District', 'align' => 'text-left'],
        ['label' => 'Region', 'align' => 'text-left'],
        ['label' => 'Users', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No branches found." :emptyRoute="route('branches.create')" emptyLinkText="Add a new branch">
        @foreach ($branches as $index => $branch)
            <tr>
                <td class="text-center">{{ $branches->firstItem() + $index }}</td>
                <td class="text-center font-semibold">{{ $branch->code }}</td>
                <td class="text-left">
                    <div class="font-semibold">{{ $branch->name }}</div>
                    <div class="muted">{{ $branch->address }}</div>
                </td>
                <td class="text-left">{{ $branch->district->name ?? '—' }}</td>
                <td class="text-left">{{ $branch->region->name ?? '—' }}</td>
                <td class="text-center">
                    @if ($branch->users_count)
                        @can('view users')
                            <a href="{{ route('users.index', ['filter' => ['branch_id' => $branch->id]]) }}" class="font-semibold">{{ $branch->users_count }}</a>
                        @else
                            {{ $branch->users_count }}
                        @endcan
                    @else
                        <span class="muted">0</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="flex justify-center gap-1">
                        @can('edit branches')
                            <a href="{{ route('branches.edit', $branch) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-app-layout>
