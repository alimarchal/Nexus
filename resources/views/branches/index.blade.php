<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            Branches
        </h2>

        <div class="flex justify-center items-center float-right">
            <button id="toggle"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
            <a href="{{ route('branches.create') }}"
               class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-950 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden md:inline-block">Add Branch</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Display session message -->
                <x-status-message/>

                @if($branches->count() > 0)
                    <div class="relative overflow-x-auto rounded-lg">
                        <table class="min-w-max w-full table-auto text-sm">
                            <thead>
                            <tr class="bg-blue-800 text-white uppercase text-sm">
                                <th class="py-2 px-2 text-center">Code</th>
                                <th class="py-2 px-2 text-center">Name</th>
                                <th class="py-2 px-2 text-center">Address</th>
                                <th class="py-2 px-2 text-center">Region</th>
                                <th class="py-2 px-2 text-center">District</th>
                                <th class="py-2 px-2 text-center print:hidden">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="text-black text-md leading-normal font-extrabold">
                            @foreach($branches as $branch)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-1 px-2 text-center">{{ $branch->code }}</td>
                                    <td class="py-1 px-2 text-center">{{ $branch->name }}</td>
                                    <td class="py-1 px-2 text-center">{{ $branch->address }}</td>
                                    <td class="py-1 px-2 text-center">{{ $branch->region->name }}</td>
                                    <td class="py-1 px-2 text-center">{{ $branch->district->name }}</td>
                                    <td class="py-1 px-2 text-center">
                                        <a href="{{ route('branches.edit', $branch) }}"
                                           class="inline-flex items-center px-4 py-2 bg-green-800 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            Edit
                                        </a>
                                        {{--  <form class="inline-block" method="POST" action="{{ route('branches.destroy', $branch) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 delete-button">
                                                Delete
                                            </button>
                                        </form>  --}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-2 py-2">
                        {{ $branches->links() }}
                    </div>
                @else
                    <p class="text-gray-700 dark:text-gray-300 text-center py-4">
                        No branches found.
                        <a href="{{ route('branches.create') }}" class="text-blue-600 hover:underline">
                            Add a new branch
                        </a>.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
