<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Status Type Details
                </h2>
                <span
                    class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
                    {{ $complaintStatusType->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $complaintStatusType->is_active ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' }}" />
                    </svg>
                    {{ $complaintStatusType->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaint-status-types.edit', $complaintStatusType) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('complaint-status-types.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Main Status Type Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $complaintStatusType->name }}</h3>
                                <p class="text-blue-100">Code: {{ $complaintStatusType->code }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm opacity-90">Status Type ID</div>
                            <div class="text-white text-xl font-bold">#{{ $complaintStatusType->id }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Information -->
                        <div class="lg:col-span-2 space-y-6">
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Basic Information</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Display Name</label>
                                        <p class="text-gray-900 font-semibold text-lg">{{ $complaintStatusType->name }}
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">System Code</label>
                                        <p
                                            class="text-gray-900 font-mono font-semibold text-lg bg-gray-50 px-2 py-1 rounded">
                                            {{ $complaintStatusType->code }}</p>
                                    </div>
                                </div>
                            </div>

                            @if ($complaintStatusType->description)
                            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Description</h4>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-purple-400">
                                    <p class="text-gray-700 leading-relaxed">{{ $complaintStatusType->description }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Sidebar Information -->
                        <div class="space-y-6">
                            <!-- Status & Timestamps -->
                            <div
                                class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Status & Timeline</h4>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm">
                                        <span class="text-sm font-medium text-gray-600">Current Status</span>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold
                                            {{ $complaintStatusType->is_active ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' }}">
                                            {{ $complaintStatusType->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                        </span>
                                    </div>
                                    <div class="p-3 bg-white rounded-lg shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-600">Created</span>
                                            <span class="text-xs text-gray-500">
                                                {{ $complaintStatusType->created_at ?
                                                $complaintStatusType->created_at->diffForHumans() : 'N/A' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-800">
                                            {{ $complaintStatusType->created_at ?
                                            $complaintStatusType->created_at->format('M d, Y \a\t H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-white rounded-lg shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-600">Last Updated</span>
                                            <span class="text-xs text-gray-500">
                                                {{ $complaintStatusType->updated_at ?
                                                $complaintStatusType->updated_at->diffForHumans() : 'N/A' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-800">
                                            {{ $complaintStatusType->updated_at ?
                                            $complaintStatusType->updated_at->format('M d, Y \a\t H:i') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Usage Statistics -->
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Usage Statistics</h4>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border-l-4 border-green-400">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Total Uses</span>
                                            <p class="text-2xl font-bold text-green-600">{{
                                                $complaintStatusType->histories()->count() }}</p>
                                        </div>
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border-l-4 border-blue-400">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">This Month</span>
                                            <p class="text-2xl font-bold text-blue-600">{{
                                                $complaintStatusType->histories()->whereMonth('created_at',
                                                now()->month)->count() }}</p>
                                        </div>
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div
                                        class="flex justify-between items-center p-3 bg-white rounded-lg shadow-sm border-l-4 border-purple-400">
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">This Week</span>
                                            <p class="text-2xl font-bold text-purple-600">{{
                                                $complaintStatusType->histories()->whereBetween('created_at',
                                                [now()->startOfWeek(), now()->endOfWeek()])->count() }}</p>
                                        </div>
                                        <div class="p-2 bg-purple-100 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add any interactive functionality here if needed
            document.addEventListener('DOMContentLoaded', function() {
                // Optional: Add tooltips or interactive elements
                console.log('Status Type Details page loaded');
            });
    </script>
    @endpush
</x-app-layout>