@php($ui = \App\Support\Ui::class)
<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Division Managers" :createRoute="route('managers.create')" createLabel="Add Manager"
            createPermission="create managers" :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('managers.index')">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-label for="filter_division_id" value="Division" />
                <select id="filter_division_id" name="filter[division_id]" class="{{ $ui::CONTROL }}">
                    <option value="">All divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" @selected((string) request('filter.division_id') === (string) $division->id)>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="filter_title" value="Title" />
                <x-input id="filter_title" name="filter[title]" type="text" class="mt-1 block w-full" :value="request('filter.title')" />
            </div>
            <div>
                <x-label for="filter_created_by_user_id" value="Created by" />
                <select id="filter_created_by_user_id" name="filter[created_by_user_id]" class="{{ $ui::CONTROL }}">
                    <option value="">Anyone</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('filter.created_by_user_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="filter_created_at" value="Created date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full" :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$managers" variant="grid" caption="Head of each head-office division" :headers="[
        ['label' => '#', 'align' => 'text-center', 'width' => '3rem'],
        ['label' => 'Division', 'align' => 'text-left'],
        ['label' => 'Manager', 'align' => 'text-left'],
        ['label' => 'Title', 'align' => 'text-left'],
        ['label' => 'Created', 'align' => 'text-left'],
        ['label' => 'Actions', 'align' => 'text-center', 'width' => '6rem'],
    ]" emptyMessage="No division managers yet." :emptyRoute="route('managers.create')" emptyLinkText="Add a manager">
        @foreach ($managers as $index => $manager)
            <tr>
                <td class="text-center">{{ $managers->firstItem() + $index }}</td>
                <td class="text-left">
                    <div class="font-semibold">{{ $manager->division->name ?? '—' }}</div>
                    @if ($manager->division?->short_name) <div class="muted">{{ $manager->division->short_name }}</div> @endif
                </td>
                <td class="text-left">
                    <div class="font-semibold">{{ $manager->managerUser->name ?? '—' }}</div>
                    <div class="muted">{{ $manager->managerUser->email ?? '' }}</div>
                </td>
                <td class="text-left">{{ $manager->title ?: '—' }}</td>
                <td class="text-left">
                    <div>{{ $manager->createdBy->name ?? '—' }}</div>
                    <div class="muted">{{ optional($manager->created_at)->format('d.m.Y') }}</div>
                </td>
                <td class="text-center">
                    <div class="flex justify-center gap-1">
                        @can('edit managers')
                            <a href="{{ route('managers.edit', $manager) }}" class="{{ $ui::ICON_EDIT }}" title="Edit">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                        @endcan
                        @can('delete managers')
                            <button type="button" x-data class="{{ $ui::ICON_DELETE }}" title="Delete"
                                x-on:click="$dispatch('open-delete-manager-modal', {{ \Illuminate\Support\Js::from(['url' => route('managers.destroy', $manager), 'name' => ($manager->managerUser->name ?? '').' — '.($manager->division->name ?? '')]) }})">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-manager-modal" title="Delete Manager" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-black">Remove <b x-text="dynamicData.name"></b> as division manager? This cannot be undone.</p>
    </x-alpine-confirmation-modal>
</x-app-layout>
