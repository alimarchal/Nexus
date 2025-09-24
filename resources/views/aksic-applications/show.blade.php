<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    AKSIC Application Details
                </h2>
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
				@switch($aksicApplication->status)
					@case('Approved') bg-green-100 text-green-800 border border-green-200 @break
					@case('Pending') bg-yellow-100 text-yellow-800 border border-yellow-200 @break
					@case('Rejected') bg-red-100 text-red-800 border border-red-200 @break
					@case('In Progress') bg-blue-100 text-blue-800 border border-blue-200 @break
					@default bg-gray-100 text-gray-800 border border-gray-200
				@endswitch">
                    {{ $aksicApplication->status ?? 'Unknown' }}
                </span>
                <span class="px-3 py-1 rounded-full text-sm font-medium shadow-sm
				@switch($aksicApplication->fee_status)
					@case('paid') bg-green-100 text-green-800 border border-green-200 @break
					@case('unpaid') bg-red-100 text-red-800 border border-red-200 @break
					@default bg-gray-100 text-gray-800 border border-gray-200
				@endswitch">
                    Fee: {{ ucfirst($aksicApplication->fee_status ?? 'Unknown') }}
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('aksic-applications.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                <button id="application-pdf-btn" type="button"
                    class="inline-flex items-center px-4 py-2 bg-indigo-700 hover:bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    title="Generate printable application PDF">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v12m0 0l-3.5-3.5M12 16l3.5-3.5M6 20h12" />
                    </svg>
                    Download PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-status-message />

            <!-- Main Application Card -->
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
                                <h3 class="text-xl font-bold text-white">{{ $aksicApplication->name }}</h3>
                                <p class="text-blue-100 font-mono">{{ $aksicApplication->application_no }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm opacity-90">Applied</div>
                            <div class="text-white text-lg font-bold">{{ $aksicApplication->created_at->format('M d, Y')
                                }}</div>
                            <div class="text-blue-100 text-sm">{{ $aksicApplication->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Personal Information -->
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Personal Information</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 space-y-3">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div><span class="font-medium text-gray-600">Full Name:</span><br>{{
                                            $aksicApplication->name }}</div>
                                        <div><span class="font-medium text-gray-600">Father's Name:</span><br>{{
                                            $aksicApplication->fatherName ?? '—' }}</div>
                                        <div><span class="font-medium text-gray-600">CNIC:</span><br>{{
                                            $aksicApplication->cnic }}</div>
                                        <div><span class="font-medium text-gray-600">Phone:</span><br>{{
                                            $aksicApplication->phone ?? '—' }}</div>
                                        <div><span class="font-medium text-gray-600">Date of Birth:</span><br>{{
                                            $aksicApplication->dob ? $aksicApplication->dob->format('M d, Y') : '—' }}
                                        </div>
                                        <div><span class="font-medium text-gray-600">CNIC Issue Date:</span><br>{{
                                            $aksicApplication->cnic_issue_date ?
                                            $aksicApplication->cnic_issue_date->format('M d, Y') : '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Business Information</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 space-y-3">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div><span class="font-medium text-gray-600">Business Name:</span><br>{{
                                            $aksicApplication->businessName ?? '—' }}</div>
                                        <div><span class="font-medium text-gray-600">Business Type:</span><br>{{
                                            $aksicApplication->businessType ?? '—' }}</div>
                                        <div><span class="font-medium text-gray-600">Tier:</span><br>{{
                                            $aksicApplication->tier ?? '—' }}</div>
                                        <div><span class="font-medium text-gray-600">Quota:</span><br>{{
                                            $aksicApplication->quota ?? '—' }}</div>
                                        <div class="col-span-2"><span class="font-medium text-gray-600">Business
                                                Address:</span><br>{{ $aksicApplication->businessAddress ?? '—' }}</div>
                                        <div class="col-span-2"><span class="font-medium text-gray-600">Permanent
                                                Address:</span><br>{{ $aksicApplication->permanentAddress ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div
                                class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Financial Details</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="text-xs text-gray-500 uppercase">Loan Amount</div>
                                            <div class="text-lg font-bold text-purple-600">
                                                @if($aksicApplication->amount)
                                                Rs. {{ number_format($aksicApplication->amount, 2) }}
                                                @else
                                                —
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="text-xs text-gray-500 uppercase">Challan Fee</div>
                                            <div class="text-lg font-bold text-purple-600">
                                                @if($aksicApplication->challan_fee)
                                                Rs. {{ number_format($aksicApplication->challan_fee, 2) }}
                                                @else
                                                —
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="text-xs text-gray-500 uppercase">Fee Status</div>
                                            <div class="text-sm font-bold text-purple-600">
                                                <span class="px-2 py-1 rounded text-xs
                                                    @if($aksicApplication->fee_status === 'paid') bg-green-100 text-green-800
                                                    @elseif($aksicApplication->fee_status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($aksicApplication->fee_status ?? 'Unknown') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="text-xs text-gray-500 uppercase">Fee Branch</div>
                                            <div class="text-sm font-bold text-purple-600">{{
                                                $aksicApplication->fee_branch_code ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Details -->
                            <div
                                class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-6 border border-orange-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Application Details</h4>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div class="space-y-3">
                                            <div><span class="font-medium text-gray-600">Application No:</span><br>{{
                                                $aksicApplication->application_no ?? '—' }}</div>
                                            <div><span class="font-medium text-gray-600">Applicant ID:</span><br>{{
                                                $aksicApplication->applicant_id ?? '—' }}</div>
                                            <div><span class="font-medium text-gray-600">Status:</span><br>
                                                <span class="px-2 py-1 rounded text-xs
                                                    @if($aksicApplication->status === 'approved') bg-green-100 text-green-800
                                                    @elseif($aksicApplication->status === 'rejected') bg-red-100 text-red-800
                                                    @elseif($aksicApplication->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($aksicApplication->status ?? 'Unknown') }}
                                                </span>
                                            </div>
                                            <div><span class="font-medium text-gray-600">Bank Status:</span><br>
                                                <span class="px-2 py-1 rounded text-xs
                                                    @if($aksicApplication->bank_status === 'approved') bg-green-100 text-green-800
                                                    @elseif($aksicApplication->bank_status === 'rejected') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($aksicApplication->bank_status ?? 'N/A') }}
                                                </span>
                                            </div>
                                            <div><span class="font-medium text-gray-600">Business Category
                                                    ID:</span><br>{{ $aksicApplication->business_category_id ?? '—' }}
                                            </div>
                                            <div><span class="font-medium text-gray-600">Business Sub-Category
                                                    ID:</span><br>{{ $aksicApplication->business_sub_category_id ?? '—'
                                                }}</div>
                                        </div>
                                        <div class="space-y-3">
                                            <div><span class="font-medium text-gray-600">District:</span><br>{{
                                                $aksicApplication->district_name ?? '—' }}
                                                @if($aksicApplication->district_id)<span
                                                    class="text-xs text-gray-500">(ID: {{ $aksicApplication->district_id
                                                    }})</span>@endif</div>
                                            <div><span class="font-medium text-gray-600">Tehsil:</span><br>{{
                                                $aksicApplication->tehsil_name ?? '—' }}
                                                @if($aksicApplication->tehsil_id)<span
                                                    class="text-xs text-gray-500">(ID: {{ $aksicApplication->tehsil_id
                                                    }})</span>@endif</div>
                                            <div><span class="font-medium text-gray-600">Chosen Branch ID:</span><br>{{
                                                $aksicApplication->applicant_choosed_branch_id ?? '—' }}</div>
                                            <div><span class="font-medium text-gray-600">Assigned Branch
                                                    ID:</span><br>{{ $aksicApplication->branch_id ?? '—' }}</div>
                                            <div><span class="font-medium text-gray-600">Challan Branch ID:</span><br>{{
                                                $aksicApplication->challan_branch_id ?? '—' }}</div>
                                            <div><span class="font-medium text-gray-600">Fee Branch Code:</span><br>{{
                                                $aksicApplication->fee_branch_code ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Education Information -->
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Education ({{
                                        $aksicApplication->educations->count() }})</h4>
                                </div>
                                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                                    @if($aksicApplication->educations->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        #</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Level</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Degree</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Institute</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Year</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($aksicApplication->educations as $index => $education)
                                                <tr class="hover:bg-gray-50">
                                                    <td
                                                        class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $index + 1 }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $education->education_level ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $education->degree_title ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $education->institute ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $education->passing_year ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $education->grade_or_percentage ?? '—' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path
                                                d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Education Records</h3>
                                        <p class="mt-1 text-sm text-gray-500">No educational qualifications have been
                                            recorded.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Status History Information -->
                            <div
                                class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-6 border border-purple-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Status History ({{
                                        $aksicApplication->statusLogs->count() }})</h4>
                                </div>
                                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                                    @if($aksicApplication->statusLogs->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        #</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Old Status</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        New Status</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Changed By</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Remarks</th>
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Date</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($aksicApplication->statusLogs->sortByDesc('created_at') as $index => $log)
                                                <tr class="hover:bg-gray-50">
                                                    <td
                                                        class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $index + 1 }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $log->old_status ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $log->new_status ?? '—' }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $log->changed_by_type ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $log->remarks ?? '—'
                                                        }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{
                                                        $log->created_at->format('M d, Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path d="M9 12h6l3-3h6l3 3h6v12a3 3 0 01-3 3H12a3 3 0 01-3-3V12z"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Status History</h3>
                                        <p class="mt-1 text-sm text-gray-500">No status changes have been recorded.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Info Sidebar -->
                        <div class="space-y-6">
                            <div
                                class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Quick Info</h4>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm">
                                        <span class="font-medium text-gray-600">Applicant ID</span>
                                        <span class="font-mono">{{ $aksicApplication->applicant_id }}</span>
                                    </div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm">
                                        <span class="font-medium text-gray-600">District</span>
                                        <span>{{ $aksicApplication->district_name ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm">
                                        <span class="font-medium text-gray-600">Tehsil</span>
                                        <span>{{ $aksicApplication->tehsil_name ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between bg-white p-3 rounded-lg shadow-sm">
                                        <span class="font-medium text-gray-600">Branch Code</span>
                                        <span>{{ $aksicApplication->fee_branch_code ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Images -->
                            <div
                                class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-800">Documents</h4>
                                </div>
                                <div class="space-y-3 text-xs">
                                    <!-- Challan Image -->
                                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-gray-600">Challan Image</span>
                                            @if($aksicApplication->challan_image_url)
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Available</span>
                                            @else
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Missing</span>
                                            @endif
                                        </div>
                                        @if($aksicApplication->challan_image_url)
                                        <div class="mt-2">
                                            <img src="{{ $aksicApplication->challan_image_url }}" alt="Challan Image"
                                                class="w-full h-32 object-cover rounded-md border border-gray-200 cursor-pointer hover:opacity-90"
                                                onclick="openImageModal('{{ $aksicApplication->challan_image_url }}', 'Challan Image')" />
                                        </div>
                                        @endif
                                    </div>
                                    <!-- CNIC Front -->
                                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-gray-600">CNIC Front</span>
                                            @if($aksicApplication->cnic_front_url)
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Available</span>
                                            @else
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Missing</span>
                                            @endif
                                        </div>
                                        @if($aksicApplication->cnic_front_url)
                                        <div class="mt-2">
                                            <img src="{{ $aksicApplication->cnic_front_url }}" alt="CNIC Front"
                                                class="w-full h-32 object-cover rounded-md border border-gray-200 cursor-pointer hover:opacity-90"
                                                onclick="openImageModal('{{ $aksicApplication->cnic_front_url }}', 'CNIC Front')" />
                                        </div>
                                        @endif
                                    </div>
                                    <!-- CNIC Back -->
                                    <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-gray-600">CNIC Back</span>
                                            @if($aksicApplication->cnic_back_url)
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Available</span>
                                            @else
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Missing</span>
                                            @endif
                                        </div>
                                        @if($aksicApplication->cnic_back_url)
                                        <div class="mt-2">
                                            <img src="{{ $aksicApplication->cnic_back_url }}" alt="CNIC Back"
                                                class="w-full h-32 object-cover rounded-md border border-gray-200 cursor-pointer hover:opacity-90"
                                                onclick="openImageModal('{{ $aksicApplication->cnic_back_url }}', 'CNIC Back')" />
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>






        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Document Image</h3>
                    <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="mb-4">
                    <img id="modalImage" src="" alt="" class="w-full h-auto rounded-lg border border-gray-300" />
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="downloadImage()"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">
                        Download
                    </button>
                    <button onclick="closeImageModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Image Modal Functions
        let currentImageUrl = '';
        
        function openImageModal(imageUrl, title) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('imageModal').classList.remove('hidden');
            currentImageUrl = imageUrl;
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('modalImage').src = '';
            currentImageUrl = '';
        }
        
        function downloadImage() {
            if (currentImageUrl) {
                const link = document.createElement('a');
                link.href = currentImageUrl;
                link.download = document.getElementById('modalTitle').textContent + '.jpg';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        // Close modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });



        // PDF Generation
        (function(){
            function gv(obj, path){ 
                var parts = path.split('.'); 
                var cur = obj; 
                for(var i = 0; i < parts.length; i++){ 
                    if(!cur || typeof cur !== 'object') return undefined; 
                    cur = cur[parts[i]]; 
                } 
                return cur; 
            }

            // Helper function to get API data safely
            function getApiData(app, path, fallback = '—') {
                if (!app.api_call_json) return fallback;
                return gv(app.api_call_json, path) || fallback;
            }

            // Add section title with audit formatting
            function addTitle(doc, txt, y) { 
                doc.setFont('helvetica', 'bold'); 
                doc.setFontSize(12); 
                doc.setTextColor(30); 
                doc.text(txt.toUpperCase(), 40, y); 
                doc.setFont('helvetica', 'normal'); 
                return y + 8; 
            }

            function log(){ 
                try{ 
                    console.log('[AKSIC PDF]', ...arguments);
                } catch(e){} 
            }

            function status(msg){
                var btn = document.getElementById('application-pdf-btn');
                if(btn){ 
                    btn.dataset.stage = msg; 
                    btn.title = 'PDF: ' + msg; 
                }
            }

            function loadScriptOnce(id, src, readyTest){
                return new Promise((res, rej) => {
                    const existing = document.getElementById(id);
                    const isReady = function(){ 
                        try{ 
                            return !readyTest || readyTest(); 
                        } catch(e){ 
                            return false; 
                        } 
                    };

                    if(existing){
                        log('script already present', id);
                        if(isReady()) return res(true);
                        existing.addEventListener('load', () => { 
                            if(isReady()) res(true); 
                            else rej(new Error('Library not ready after load ' + id)); 
                        });
                        existing.addEventListener('error', () => rej(new Error('Load failed ' + src)));
                        return;
                    }

                    const s = document.createElement('script'); 
                    s.id = id; 
                    s.src = src; 
                    s.async = true;
                    s.onload = () => { 
                        log('loaded', id); 
                        if(isReady()) res(true); 
                        else rej(new Error('Library not ready ' + id)); 
                    };
                    s.onerror = () => { 
                        log('failed load', id, src); 
                        rej(new Error('Load failed ' + src)); 
                    };
                    document.head.appendChild(s);
                });
            }

            async function ensureLibs(){
                async function attempt(id, primary, fallback, test){
                    try{ 
                        await loadScriptOnce(id, primary, test); 
                    } catch(e){ 
                        if(fallback) { 
                            log('primary failed, trying fallback', id); 
                            await loadScriptOnce(id + '-fb', fallback, test); 
                        } else throw e; 
                    }
                }

                if(!(window.jspdf && window.jspdf.jsPDF)) {
                    await attempt('jspdf-core', 'https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js', 'https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js', () => window.jspdf && window.jspdf.jsPDF);
                }
                
                if(!(window.jspdf && window.jspdf.jsPDF)) {
                    throw new Error('jsPDF load failed (after load attempt)');
                }
                
                if(!window.jspdf.jsPDF.API.autoTable) {
                    await attempt('jspdf-autotable', 'https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js', 'https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js', () => window.jspdf && window.jspdf.jsPDF && !!window.jspdf.jsPDF.API.autoTable);
                }
            }

            function fmt(dt){ 
                if(!dt) return '—'; 
                try{ 
                    return new Date(dt).toLocaleString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch(e){
                    return dt;
                } 
            }

            async function runPdf(){ 
                const btn = document.getElementById('application-pdf-btn'); 
                if(!btn){ 
                    log('runPdf: no button'); 
                    return; 
                }

                log('runPdf invoked'); 
                status('clicked');
                
                try{ 
                    btn.disabled = true; 
                    btn.classList.add('opacity-50'); 
                    btn.textContent = 'Building...'; 
                    status('loading libs'); 
                    
                    await ensureLibs(); 
                    log('libs ready'); 
                    status('libs ready'); 
                    
                    const { jsPDF } = window.jspdf;
                    const url = @json(route('aksic-applications.pdf', $aksicApplication)); 
                    
                    log('fetching', url); 
                    status('fetching data'); 
                    
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }, 
                        credentials: 'same-origin'
                    }); 
                    
                    log('fetch status', res.status); 
                    
                    if(!res.ok) throw new Error('Fetch failed ' + res.status); 
                    
                    const data = await res.json(); 
                    const app = data.application; 
                    log('data received keys', Object.keys(app)); 
                    status('building pdf');
                    const doc = new jsPDF('p', 'pt');
                    
                    // Header
                    doc.setFont('helvetica', 'bold'); 
                    doc.setFontSize(16); 
                    doc.setTextColor(20); 
                    doc.text('THE BANK OF AZAD JAMMU AND KASHMIR', 40, 40);
                    
                    doc.setFontSize(13); 
                    doc.text('AKSIC APPLICATION REPORT', 40, 60); 
                    
                    doc.setFont('helvetica', 'normal'); 
                    doc.setFontSize(9); 
                    doc.setTextColor(70);
                    doc.text('Generated: ' + new Date().toLocaleDateString(), 40, 74); 
                    doc.text('Application ID: ' + (app.applicant_id || '—'), 300, 74); 
                    doc.text('Status: ' + (app.status || '—'), 480, 74);
                    
                    doc.setDrawColor(0); 
                    doc.setLineWidth(0.5); 
                    doc.line(40, 78, 555, 78);
                    
                    let y = 95;
                    
                    // Application Summary Table - Complete Data (2 columns)
                    const summaryData = [
                        // Basic Information
                        ['Application No', app.application_no || '—'],
                        ['Applicant ID', app.applicant_id || '—'],
                        ['Applicant Name', app.name || '—'],
                        ['Father Name', app.father_name || app.fatherName || '—'],
                        ['CNIC', app.cnic || '—'],
                        ['Phone', app.phone || '—'],
                        ['Date of Birth', app.dob ? fmt(app.dob) : '—'],
                        ['CNIC Issue Date', app.cnic_issue_date ? fmt(app.cnic_issue_date) : '—'],
                        
                        // Business Information
                        ['Business Name', app.businessName || '—'],
                        ['Business Type', app.businessType || '—'],
                        ['Tier', app.tier || '—'],
                        ['Quota', app.quota || '—'],
                        ['Business Address', (app.businessAddress || '—').length > 60 ? (app.businessAddress || '—').substring(0, 60) + '...' : (app.businessAddress || '—')],
                        ['Permanent Address', (app.permanentAddress || '—').length > 60 ? (app.permanentAddress || '—').substring(0, 60) + '...' : (app.permanentAddress || '—')],
                        
                        // Financial Information
                        ['Loan Amount', app.amount ? 'Rs. ' + Number(app.amount).toLocaleString() : '—'],
                        ['Challan Fee', app.challan_fee ? 'Rs. ' + Number(app.challan_fee).toLocaleString() : '—'],
                        ['Fee Status', app.fee_status || '—'],
                        ['Status', app.status || '—'],
                        ['Bank Status', app.bank_status || '—'],
                        ['Fee Branch Code', app.fee_branch_code || '—'],
                        
                        // Location Information
                        ['District', app.district_name || getApiData(app, 'district.name')],
                        ['Tehsil', app.tehsil_name || getApiData(app, 'tehsil.name')],
                        ['District ID', app.district_id || '—'],
                        ['Tehsil ID', app.tehsil_id || '—'],
                        
                        // Branch Information
                        ['Chosen Branch', getApiData(app, 'applicant_choosed_branch_detail.name')],
                        ['Assigned Branch', getApiData(app, 'branch_detail.name')],
                        ['Challan Branch', getApiData(app, 'challan_branch_detail.name')],
                        ['Fee Branch', getApiData(app, 'fee_branch.name')],
                        ['Business Category', getApiData(app, 'business_category.name')],
                        ['Business Sub-Category', getApiData(app, 'business_sub_category.name')],
                        ['Applied Date', fmt(app.created_at)],
                        ['Last Updated', fmt(app.updated_at)],
                        
                        // Additional API Information
                        ['Fee Branch Code (API)', getApiData(app, 'fee_branch.branch_code')],
                        ['Challan Branch Code (API)', getApiData(app, 'challan_branch_detail.branch_code')]
                    ];

                    doc.autoTable({
                        startY: y,
                        body: summaryData,
                        styles: {
                            fontSize: 8,
                            cellPadding: 3,
                            lineColor: [0, 0, 0],
                            lineWidth: 0.1
                        },
                        columnStyles: {
                            0: { cellWidth: 140, fontStyle: 'bold' },
                            1: { cellWidth: 370 }
                        },
                        tableWidth: 510,
                        theme: 'grid'
                    });

                    y = doc.lastAutoTable.finalY + 20;

                    // Education Section
                    if (app.educations && app.educations.length > 0) {
                        y = addTitle(doc, 'Educational Qualifications', y);

                        const educationData = app.educations.map((edu, index) => [
                            index + 1,
                            edu.education_level || '—',
                            edu.degree_title || '—',
                            edu.institute || '—',
                            edu.passing_year || '—',
                            edu.grade_or_percentage || '—'
                        ]);

                        doc.autoTable({
                            startY: y,
                            head: [['#', 'LEVEL', 'DEGREE', 'INSTITUTE', 'YEAR', 'GRADE']],
                            body: educationData,
                            styles: {
                                fontSize: 7,
                                cellPadding: 2,
                                lineColor: [0, 0, 0],
                                lineWidth: 0.1
                            },
                            headStyles: {
                                fillColor: [0, 0, 0],
                                textColor: [255, 255, 255]
                            },
                            theme: 'grid'
                        });

                        y = doc.lastAutoTable.finalY + 20;
                    }

                    // Status History Section
                    if (app.status_logs && app.status_logs.length > 0) {
                        y = addTitle(doc, 'Status History', y);

                        const statusData = app.status_logs.map((log, index) => [
                            index + 1,
                            log.old_status || '—',
                            log.new_status || '—',
                            log.changed_by_type || '—',
                            log.remarks || '—',
                            fmt(log.created_at)
                        ]);

                        doc.autoTable({
                            startY: y,
                            head: [['#', 'OLD STATUS', 'NEW STATUS', 'CHANGED BY', 'REMARKS', 'DATE']],
                            body: statusData,
                            styles: {
                                fontSize: 6.5,
                                cellPadding: 2,
                                lineColor: [0, 0, 0],
                                lineWidth: 0.1
                            },
                            headStyles: {
                                fillColor: [0, 0, 0],
                                textColor: [255, 255, 255]
                            },
                            theme: 'grid'
                        });

                        y = doc.lastAutoTable.finalY + 20;
                    }

                    // Documents Section with Images
                    if (app.challan_image_url || app.cnic_front_url || app.cnic_back_url) {



                    }

                    // Footer - using audit style
                    const pageCount = doc.getNumberOfPages();
                    for (let i = 1; i <= pageCount; i++) {
                        doc.setPage(i);
                        const footerText = `AKSIC Application # ${app.application_no || app.id || '—'} | Page ${i} of ${pageCount}`;
                        doc.setFontSize(8);
                        doc.setTextColor(90);
                        const pageWidth = doc.internal.pageSize.getWidth();
                        const textWidth = doc.getTextWidth(footerText);
                        doc.text(footerText, (pageWidth / 2) - (textWidth / 2), doc.internal.pageSize.getHeight() - 12);
                    }

                    // Save the PDF
                    const filename = `AKSIC_Application_${app.application_no || app.applicant_id || 'Unknown'}.pdf`;
                    doc.save(filename);
                    
                    status('completed');
                    log('PDF generated successfully');
                    
                } catch (e) {
                    log('PDF error', e);
                    status('error: ' + e.message);
                    alert('PDF generation failed: ' + e.message);
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50');
                    btn.textContent = 'Download PDF';
                }
            }

            // Attach event listener
            const btn = document.getElementById('application-pdf-btn');
            if (btn) {
                btn.addEventListener('click', runPdf);
            }
        })();
    </script>
    @endpush
</x-app-layout>