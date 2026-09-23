<x-app-layout>
    <x-slot name="header">
        <x-page-header title="AKSIC Markup Budget" :createRoute="route('aksic-budgets.create')" createLabel="New Budget"
            createPermission="manage aksic budget" :showSearch="false" :showRefresh="false" backRoute="aksic.index" />
    </x-slot>
    @include('aksics._grid-style')
    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <x-status-message />
            <div class="bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">No budget is active, so approvals are not checked against a markup budget.</p>
                <table class="aksic-grid">
                    <thead><tr><th>Title</th><th class="num">Total (Rs)</th><th class="ctr">Status</th><th class="ctr">Open</th></tr></thead>
                    <tbody>
                        @forelse ($budgets as $b)
                            <tr>
                                <td>{{ $b->title }}</td>
                                <td class="num">{{ number_format((float) $b->total_amount, 2) }}</td>
                                <td class="ctr">{{ $b->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="ctr"><a class="text-blue-700 hover:underline" href="{{ route('aksic-budgets.show', $b) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ctr">No budget created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
