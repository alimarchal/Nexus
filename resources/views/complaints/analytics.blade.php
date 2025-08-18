<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Complaint Analytics & Reports
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Complaints
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-status-message />

            <!-- Date Range Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Analytics by Date Range</h3>
                    <form method="GET" action="{{ route('complaints.analytics') }}" class="flex items-end space-x-4">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from"
                                value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to"
                                value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Apply Filter
                        </button>
                        <a href="{{ route('complaints.analytics') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Reset
                        </a>
                    </form>
                </div>
            </div>

            <!-- Key Metrics Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Complaints -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Total Complaints</p>
                            <p class="text-3xl font-bold text-blue-900">{{ number_format($totalComplaints) }}</p>
                        </div>
                        <div class="p-3 bg-blue-200 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Resolved Complaints -->
                <div
                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600">Resolved Complaints</p>
                            <p class="text-3xl font-bold text-green-900">{{ number_format($resolvedComplaints) }}</p>
                            <p class="text-sm text-green-700">
                                {{ $totalComplaints > 0 ? number_format(($resolvedComplaints / $totalComplaints) * 100,
                                1) : 0 }}% Resolution Rate
                            </p>
                        </div>
                        <div class="p-3 bg-green-200 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Open Complaints -->
                <div
                    class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">Open Complaints</p>
                            <p class="text-3xl font-bold text-yellow-900">{{ number_format($openComplaints) }}</p>
                            <p class="text-sm text-yellow-700">Currently Active</p>
                        </div>
                        <div class="p-3 bg-yellow-200 rounded-full">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Overdue Complaints -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-600">Overdue Complaints</p>
                            <p class="text-3xl font-bold text-red-900">{{ number_format($overdueComplaints) }}</p>
                            <p class="text-sm text-red-700">Need Immediate Attention</p>
                        </div>
                        <div class="p-3 bg-red-200 rounded-full">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5C3.312 16.333 4.275 18 5.814 18z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Average Resolution Time -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-purple-600">Average Resolution Time</p>
                                    <p class="text-2xl font-bold text-purple-900">
                                        @if($avgResolutionTime)
                                        {{ number_format($avgResolutionTime / 60, 1) }} hours
                                        @else
                                        N/A
                                        @endif
                                    </p>
                                </div>
                                <div class="p-2 bg-purple-200 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600">SLA Compliance</p>
                                    <p class="text-2xl font-bold text-indigo-900">{{ number_format($slaCompliance, 1)
                                        }}%</p>
                                </div>
                                <div class="p-2 bg-indigo-200 rounded-full">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trend (Last 12 Months)</h3>
                        <canvas id="monthlyTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Distribution</h3>
                        <div class="space-y-3">
                            @foreach($statusDistribution as $status)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3
                                        @switch($status->status)
                                            @case('Open') bg-yellow-400 @break
                                            @case('In Progress') bg-blue-400 @break
                                            @case('Pending') bg-orange-400 @break
                                            @case('Resolved') bg-green-400 @break
                                            @case('Closed') bg-gray-400 @break
                                            @case('Reopened') bg-red-400 @break
                                            @default bg-gray-400
                                        @endswitch">
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $status->status }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $status->count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Priority Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Distribution</h3>
                        <div class="space-y-3">
                            @foreach($priorityDistribution as $priority)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3
                                        @switch($priority->priority)
                                            @case('Low') bg-green-400 @break
                                            @case('Medium') bg-yellow-400 @break
                                            @case('High') bg-orange-400 @break
                                            @case('Critical') bg-red-400 @break
                                            @default bg-gray-400
                                        @endswitch">
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $priority->priority }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $priority->count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Source Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Source Distribution</h3>
                        <div class="space-y-3">
                            @foreach($sourceDistribution as $source)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3 bg-blue-400"></div>
                                    <span class="text-sm font-medium text-gray-700">{{ $source->source }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $source->count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Branch Performance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resolved</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($branchPerformance as $branch)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $branch->branch_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $branch->total_complaints }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $branch->resolved_complaints }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $branch->total_complaints > 0 ?
                                            number_format(($branch->resolved_complaints / $branch->total_complaints) *
                                            100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No data available
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- User Performance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">User Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Assigned</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Resolved</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($userPerformance as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $user->user_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->assigned_complaints }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->resolved_complaints }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->assigned_complaints > 0 ?
                                            number_format(($user->resolved_complaints / $user->assigned_complaints) *
                                            100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No data available
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Trend Chart
            const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
            const monthlyTrendData = @json($monthlyTrend);
            
            const months = monthlyTrendData.map(item => {
                const date = new Date(item.year, item.month - 1);
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });
            
            const counts = monthlyTrendData.map(item => item.count);
            
            new Chart(monthlyTrendCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Complaints',
                        data: counts,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>