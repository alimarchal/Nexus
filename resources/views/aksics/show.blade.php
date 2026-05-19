<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block print:hidden">
            AKSIC Details: {{ $aksic->application_no }}
        </h2>
        <div class="flex justify-center items-center float-right print:hidden">
            <button onclick="window.print()"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Print
            </button>
            @can('edit aksics')
                <a href="{{ route('aksic.edit', $aksic) }}"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </a>
            @endcan
            @can('delete aksics')
                <form method="POST" action="{{ route('aksic.destroy', $aksic) }}" class="inline-block"
                    onsubmit="return confirm('Delete this AKSIC record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center ml-2 px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-800 focus:bg-red-800 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Delete
                    </button>
                </form>
            @endcan
            <a href="{{ route('aksic.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4 pb-16 print:p-0 print:m-0">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl print:shadow-none">
            <style>
                @media print {
                    @page {
                        /* size: A4 landscape; */
                        margin: 0.6cm;
                    }

                    body {
                        margin: 0;
                        padding: 0;
                        color: #000;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        page-break-inside: auto;
                    }

                    th,
                    td {
                        border: 1px solid #000 !important;
                        padding: 3px 5px !important;
                        font-size: 10px !important;
                        color: #000 !important;
                    }

                    thead {
                        display: table-header-group;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    .print-title {
                        font-size: 16px !important;
                    }
                }
            </style>

            <div class="p-4 print:p-0">
                <div class="text-center mb-4">
                    <h1 class="print-title text-xl font-bold text-gray-900 dark:text-gray-100">AKSIC Amortization
                        Schedule</h1>
                    <p class="text-sm text-gray-700 dark:text-gray-300 print:text-black">
                        Application No: {{ $aksic->application_no }} | Generated: {{ now()->format('d-M-Y h:i A') }}
                    </p>
                </div>

                <table
                    class="mb-4 w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-300 print:text-black">
                    <caption class="caption-top text-left p-2 font-bold">Main Information</caption>
                    <tbody>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Application No</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->application_no }}</td>
                            <th class="px-2 py-2 border border-black">Status</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->status }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Applicant Name</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->name }}</td>
                            <th class="px-2 py-2 border border-black">Father Name</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->father_name }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">CNIC</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->cnic }}</td>
                            <th class="px-2 py-2 border border-black">CNIC Issue Date</th>
                            <td class="px-2 py-2 border border-black">
                                {{ $aksic->cnic_issue_date?->format('d-M-Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Date of Birth</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->dob?->format('d-M-Y') ?? '-' }}</td>
                            <th class="px-2 py-2 border border-black">Phone</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->phone ?? '-' }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Business Category</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->businessCategory?->name ?? '-' }}</td>
                            <th class="px-2 py-2 border border-black">Business Sub Category</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->businessSubCategory?->name ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Business Name</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->business_name ?? '-' }}</td>
                            <th class="px-2 py-2 border border-black">Business Type</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->business_type ?? '-' }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Branch</th>
                            <td class="px-2 py-2 border border-black">
                                {{ $aksic->branch ? $aksic->branch->code . ' - ' . $aksic->branch->name : '-' }}</td>
                            <th class="px-2 py-2 border border-black">Quota</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->quota ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">District Rule</th>
                            <td class="px-2 py-2 border border-black">
                                {{ $aksic->aksicRule?->district_name ?? $aksic->district_name ?? $aksic->district?->name ?? '-' }}
                            </td>
                            <th class="px-2 py-2 border border-black">Rule Beneficiaries</th>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ $aksic->aksicRule ? number_format($aksic->aksicRule->proposed_beneficiaries) : '-' }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Business Nature</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->business_type ?? '-' }}</td>
                            <th class="px-2 py-2 border border-black">Startup / New</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->is_startup_business ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Site Visit Completed</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->site_visit_completed ? 'Yes' : 'No' }}</td>
                            <th class="px-2 py-2 border border-black">Site Visit Date</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->site_visit_date?->format('d-M-Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Principal Amount</th>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->principal_amount, 2) }}</td>
                            <th class="px-2 py-2 border border-black">Tenure</th>
                            <td class="px-2 py-2 border border-black text-right">{{ $aksic->tenure }} Months</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">KIBOR</th>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->kibor_rate, 2) }}%</td>
                            <th class="px-2 py-2 border border-black">Spread</th>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->spread_rate, 2) }}%</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Total Rate</th>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->total_rate, 2) }}%</td>
                            <th class="px-2 py-2 border border-black">Disbursement Date</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->disbursement_date?->format('d-M-Y') }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Sanction Date</th>
                            <td class="px-2 py-2 border border-black">
                                {{ $aksic->sanction_date?->format('d-M-Y') ?? '-' }}</td>
                            <th class="px-2 py-2 border border-black">Created By</th>
                            <td class="px-2 py-2 border border-black">{{ $aksic->creator?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Business Address</th>
                            <td colspan="3" class="px-2 py-2 border border-black">{{ $aksic->business_address ?? '-' }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                            <th class="px-2 py-2 border border-black">Permanent Address</th>
                            <td colspan="3" class="px-2 py-2 border border-black">{{ $aksic->permanent_address ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table
                    class="w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-300 print:text-black">
                    <caption class="caption-top text-left p-2 font-bold">Amortization Schedule</caption>
                    <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700 print:bg-gray-200">
                        <tr>
                            <th class="px-2 py-2 border border-black text-center">#</th>
                            <th class="px-2 py-2 border border-black text-left">Date</th>
                            <th class="px-2 py-2 border border-black text-right">Principal OS</th>
                            <th class="px-2 py-2 border border-black text-right">Installment</th>
                            <th class="px-2 py-2 border border-black text-right">Product</th>
                            <th class="px-2 py-2 border border-black text-right">Interest / Day</th>
                            <th class="px-2 py-2 border border-black text-right">Total Interest</th>
                            <th class="px-2 py-2 border border-black text-center">Days</th>
                            <th class="px-2 py-2 border border-black text-right">Total Installment</th>
                            <th class="px-2 py-2 border border-black text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aksic->amortizations as $row)
                            <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-700 print:bg-gray-100' : '' }}">
                                <td class="px-2 py-2 border border-black text-center">{{ $row->installment_no }}</td>
                                <td class="px-2 py-2 border border-black text-left">{{ $row->due_date->format('d-M-Y') }}
                                </td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->principal_amount_os, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->installment_per_month, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->product, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->interest_rate_per_month, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->total_interest, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-center">{{ $row->days }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->total_installment, 2) }}</td>
                                <td class="px-2 py-2 border border-black text-right">
                                    {{ number_format((float) $row->principal_balance_after_installment, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-bold bg-gray-100 dark:bg-gray-700 print:bg-gray-200">
                        <tr>
                            <td colspan="6" class="px-2 py-2 border border-black text-right">Totals</td>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->amortizations->sum('total_interest'), 2) }}</td>
                            <td class="px-2 py-2 border border-black text-center">
                                {{ $aksic->amortizations->sum('days') }}</td>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->amortizations->sum('total_installment'), 2) }}</td>
                            <td class="px-2 py-2 border border-black text-right">0.00</td>
                        </tr>
                        <tr class="bg-gray-200 dark:bg-gray-600 print:bg-gray-300">
                            <td colspan="3" class="px-2 py-2 border border-black text-right">Total Principal</td>
                            <td class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->amortizations->sum('installment_per_month'), 2) }}</td>
                            <td colspan="2" class="px-2 py-2 border border-black text-right">Grand Total Payable</td>
                            <td colspan="3" class="px-2 py-2 border border-black text-right">
                                {{ number_format((float) $aksic->amortizations->sum('total_installment'), 2) }}</td>
                            <td class="px-2 py-2 border border-black text-right">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
