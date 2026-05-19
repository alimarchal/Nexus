<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            AKSIC Rule: {{ $aksicRule->district_name }}
        </h2>
        <div class="flex justify-center items-center float-right">
            @can('edit aksic rules')
                <a href="{{ route('aksic-rules.edit', $aksicRule) }}"
                    class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </a>
            @endcan
            <a href="{{ route('aksic-rules.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4 pb-16">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <table class="w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-300">
                    <tbody>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="px-2 py-2 border border-black">District</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->district_name }}</td>
                            <th class="px-2 py-2 border border-black">Status</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Population %</th>
                            <td class="px-2 py-2 border border-black">{{ number_format((float) $aksicRule->population_percentage, 2) }}%</td>
                            <th class="px-2 py-2 border border-black">Proposed Beneficiaries</th>
                            <td class="px-2 py-2 border border-black">{{ number_format($aksicRule->proposed_beneficiaries) }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="px-2 py-2 border border-black">Male</th>
                            <td class="px-2 py-2 border border-black">{{ number_format((float) $aksicRule->male_percentage, 2) }}%</td>
                            <th class="px-2 py-2 border border-black">Female</th>
                            <td class="px-2 py-2 border border-black">{{ number_format((float) $aksicRule->female_percentage, 2) }}%</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Special Person</th>
                            <td class="px-2 py-2 border border-black">{{ number_format((float) $aksicRule->special_person_percentage, 2) }}%</td>
                            <th class="px-2 py-2 border border-black">Transgender</th>
                            <td class="px-2 py-2 border border-black">{{ number_format((float) $aksicRule->transgender_percentage, 2) }}%</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="px-2 py-2 border border-black">Site Visit Required</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->requires_site_visit ? 'Yes' : 'No' }}</td>
                            <th class="px-2 py-2 border border-black">Business Nature Required</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->requires_business_nature ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-black">Used Records</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->aksics_count }}</td>
                            <th class="px-2 py-2 border border-black">Created By</th>
                            <td class="px-2 py-2 border border-black">{{ $aksicRule->creator?->name ?? '-' }}</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="px-2 py-2 border border-black">Notes</th>
                            <td colspan="3" class="px-2 py-2 border border-black">{{ $aksicRule->notes ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
