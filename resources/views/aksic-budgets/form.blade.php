@php
    $editing = $budget->exists;
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$editing ? 'Edit Budget Settings' : 'New Budget Version'" :showSearch="false" :showRefresh="false"
            :backRoute="$editing ? route('aksic-budgets.show', $budget) : route('aksic-budgets.index')" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-4 sm:px-6 lg:px-8">
            <x-validation-errors />

            <form method="POST" action="{{ $editing ? route('aksic-budgets.update', $budget) : route('aksic-budgets.store') }}"
                class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                @csrf
                @if ($editing) @method('PUT') @endif

                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400">Markup budget</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if ($editing)
                            Changes are recorded in the revision history with the reason you give. District allocations are changed from the budget page (Enhance / Reduce / Redistribute).
                        @else
                            District allocations are created automatically as total &times; population % of each active AKSIC rule, exactly like the allocation sheet.
                        @endif
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="{{ $lbl }}" for="title">Title <span class="text-red-600">*</span></label>
                        <input id="title" name="title" required class="{{ $control }}" value="{{ old('title', $budget->title ?? 'PM Youth Loan Scheme -- Markup Allocation') }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="total_amount">Total markup budget (Rs) <span class="text-red-600">*</span></label>
                        <input id="total_amount" name="total_amount" type="number" step="0.01" min="0.01" required class="{{ $control }}" value="{{ old('total_amount', $budget->total_amount) }}">
                        <p class="mt-1 text-xs text-gray-500">e.g. 994225000 for Rs 994.225 million</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="enforcement">At approval, when a limit is exceeded <span class="text-red-600">*</span></label>
                        <select id="enforcement" name="enforcement" class="{{ $control }}">
                            @foreach (\App\Models\AksicBudget::ENFORCEMENTS as $key => $label)
                                <option value="{{ $key }}" @selected(old('enforcement', $budget->enforcement) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="existing_business_percentage">Existing business share % <span class="text-red-600">*</span></label>
                        <input id="existing_business_percentage" name="existing_business_percentage" type="number" step="0.01" min="0" max="100" required class="{{ $control }}"
                            value="{{ old('existing_business_percentage', $budget->existing_business_percentage) }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="new_business_percentage">New business share % <span class="text-red-600">*</span></label>
                        <input id="new_business_percentage" name="new_business_percentage" type="number" step="0.01" min="0" max="100" required class="{{ $control }}"
                            value="{{ old('new_business_percentage', $budget->new_business_percentage) }}">
                        <p class="mt-1 text-xs text-gray-500">Existing + New must total 100.</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="reference_no">Sanction / letter no.</label>
                        <input id="reference_no" name="reference_no" class="{{ $control }}" value="{{ old('reference_no', $budget->reference_no) }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="reference_date">Letter date (D.M.Y)</label>
                        <input id="reference_date" name="reference_date" placeholder="dd.mm.yyyy" class="{{ $control }}"
                            value="{{ old('reference_date', $budget->reference_date?->format('d.m.Y')) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $lbl }}" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="2" class="{{ $control }}">{{ old('notes', $budget->notes) }}</textarea>
                    </div>
                    @if ($editing)
                        <div class="md:col-span-2">
                            <label class="{{ $lbl }}" for="change_reason">Reason for this change <span class="text-red-600">*</span></label>
                            <input id="change_reason" name="change_reason" required class="{{ $control }}" value="{{ old('change_reason') }}">
                        </div>
                    @else
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 md:col-span-2">
                            <input type="checkbox" name="activate" value="1" checked class="rounded border-gray-300">
                            Make this the active budget now (the current active budget becomes inactive)
                        </label>
                    @endif
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-3 dark:border-gray-700">
                    <x-button class="bg-blue-950 hover:bg-green-800">{{ $editing ? 'Save settings' : 'Create budget' }}</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
