<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Settings User-Module Managers" :createRoute="route('managers.create')"
            createLabel="" :showSearch="true" :showRefresh="true" backRoute="user.module" />
    </x-slot>

    <x-filter-section :action="route('managers.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-label for="filter_division_id" value="Division" />
                <select id="filter_division_id" name="filter[division_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Divisions">
                    <option value="">All Divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" {{ (string) request('filter.division_id') === (string) $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_title" value="Title" />
                <x-input id="filter_title" name="filter[title]" type="text" class="mt-1 block w-full"
                    :value="request('filter.title')" />
            </div>

            <div>
                <x-label for="filter_created_by_user_id" value="Created By" />
                <select id="filter_created_by_user_id" name="filter[created_by_user_id]"
                    class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                    data-placeholder="All Creators">
                    <option value="">All Creators</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (string) request('filter.created_by_user_id') === (string) $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="filter_created_at" value="Created Date" />
                <x-input id="filter_created_at" name="filter[created_at]" type="date" class="mt-1 block w-full"
                    :value="request('filter.created_at')" />
            </div>
        </div>
    </x-filter-section>

    <x-data-table :items="$managers" :headers="[
        ['label' => '#', 'align' => 'text-center'],
        ['label' => 'Division', 'align' => 'text-center'],
        ['label' => 'Title', 'align' => 'text-center'],
        ['label' => 'Manager', 'align' => 'text-center'],
        ['label' => 'Created By', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center'],
    ]" emptyMessage="No managers found."
        :emptyRoute="route('managers.create')" emptyLinkText="Add a new manager">
        @foreach ($managers as $index => $manager)
            <tr class="border-b border-gray-200 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="px-2 py-1 text-center">
                    {{ $managers->firstItem() + $index }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $manager->division->name ?? 'N/A' }}
                </td>
                <td class="px-2 py-1 text-center font-semibold">
                    {{ $manager->title }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $manager->managerUser->name ?? 'N/A' }}
                </td>
                <td class="px-2 py-1 text-center">
                    {{ $manager->createdBy->name ?? 'N/A' }}
                </td>
                <td class="px-2 py-1 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('managers.edit', $manager) }}"
                            class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-colors duration-150"
                            title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>

                        <button type="button" x-data
                            x-on:click="$dispatch('open-delete-manager-modal', { url: '{{ route('managers.destroy', $manager) }}', number: '{{ $manager->title }}' })"
                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-colors duration-150"
                            title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-alpine-confirmation-modal eventName="open-delete-manager-modal" title="Delete Manager" confirmButtonText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700" csrfMethod="DELETE">
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this manager? This action cannot be undone.
        </p>
    </x-alpine-confirmation-modal>
</x-app-layout>
