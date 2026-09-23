@php
    $dueDiligence = $account?->dueDiligences->first();
    $selectedIncome = old('income_sources', $dueDiligence?->incomeSources->pluck('id')->all() ?? []);
    $selectedWealth = old('wealth_sources', $dueDiligence?->wealthSources->pluck('id')->all() ?? []);
    $selectedPurposes = old('account_purposes', $dueDiligence?->accountPurposes->pluck('id')->all() ?? []);
    $selectedCounterParties = old('counter_party_types', $dueDiligence?->counterPartyTypes->pluck('id')->all() ?? []);
    $selectedCredit = old('credit_modes', $dueDiligence?->transactionModes->where('pivot.direction', 'credit')->pluck('id')->all() ?? []);
    $selectedDebit = old('debit_modes', $dueDiligence?->transactionModes->where('pivot.direction', 'debit')->pluck('id')->all() ?? []);

    $ubos = old('ubos', $account?->ultimateBeneficialOwners->map(fn ($row) => [
        'name' => $row->name,
        'relationship_id' => $row->relationship_id,
        'identification_document_type_id' => $row->identification_document_type_id,
        'identification_number' => $row->identification_number,
        'declaration_form_received' => $row->declaration_form_received,
    ])->all() ?? []);

    while (count($ubos) < 2) {
        $ubos[] = [];
    }

    $documentsOnFile = $accountOpeningRequest->documents->keyBy('document_type_id');
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="Due Diligence & Documents" enctype="multipart/form-data">

    @if (! $account)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
            Complete the <a class="font-semibold underline"
                href="{{ route('account-openings.steps.edit', [$accountOpeningRequest, 'account']) }}">Account</a>
            step first &mdash; due diligence is recorded against the account.
        </div>
    @else
        <x-aof-section title="Type of Customer" reference="For Bank Use Only — Walk In / Marketed / Referred By">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <x-label for="customer_source" value="Type of Customer" />
                    <select id="customer_source" name="customer_source"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach (['walk_in' => 'Walk In', 'marketed' => 'Marketed', 'referred' => 'Referred By'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('customer_source', $dueDiligence?->customer_source) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="referred_by" value="Referred By" />
                    <x-input id="referred_by" name="referred_by" type="text" class="mt-1 block w-full"
                        :value="old('referred_by', $dueDiligence?->referred_by)" />
                    <x-input-error for="referred_by" class="mt-1" />
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center text-sm">
                        <input type="hidden" name="physical_verification_conducted" value="0">
                        <input type="checkbox" name="physical_verification_conducted" value="1"
                            @checked(old('physical_verification_conducted', $dueDiligence?->physical_verification_conducted))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2">Physical Verification Conducted</span>
                    </label>
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center text-sm">
                        <input type="hidden" name="proscribed_list_cleared" value="0">
                        <input type="checkbox" name="proscribed_list_cleared" value="1"
                            @checked(old('proscribed_list_cleared', $dueDiligence?->proscribed_list_cleared))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2">Name Cleared From Proscribed List</span>
                    </label>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Source of Income / Occupation / Profession"
            reference="AOF-Individual page 14 / AOF-Entity page 17">
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                @foreach ($incomeSources as $source)
                    <label class="flex items-start space-x-2 text-sm">
                        <input type="checkbox" name="income_sources[]" value="{{ $source->id }}"
                            @checked(in_array($source->id, (array) $selectedIncome, true))
                            class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>{{ $source->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error for="income_sources" class="mt-1" />

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <x-label value="Employer Name" />
                    <x-input name="employer_name" type="text" class="mt-1 block w-full"
                        :value="old('employer_name', $dueDiligence?->employer_name)" />
                </div>
                <div>
                    <x-label value="Designation" />
                    <x-input name="employer_designation" type="text" class="mt-1 block w-full"
                        :value="old('employer_designation', $dueDiligence?->employer_designation)" />
                </div>
                <div>
                    <x-label value="Address of Employer" />
                    <x-input name="employer_address" type="text" class="mt-1 block w-full"
                        :value="old('employer_address', $dueDiligence?->employer_address)" />
                </div>

                <div>
                    <x-label value="Employer of Fund Provider" />
                    <x-input name="fund_provider_employer" type="text" class="mt-1 block w-full"
                        :value="old('fund_provider_employer', $dueDiligence?->fund_provider_employer)" />
                </div>
                <div>
                    <x-label value="ID Document No. of Fund Provider" />
                    <x-input name="fund_provider_id_number" type="text" class="mt-1 block w-full"
                        :value="old('fund_provider_id_number', $dueDiligence?->fund_provider_id_number)" />
                </div>
                <div>
                    <x-label value="Relationship with Fund Provider" />
                    <select name="fund_provider_relationship_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($relationships->where('context_fund_provider', true) as $relationship)
                            <option value="{{ $relationship->id }}"
                                @selected(old('fund_provider_relationship_id', $dueDiligence?->fund_provider_relationship_id) === $relationship->id)>
                                {{ $relationship->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label value="Business Name" />
                    <x-input name="business_name" type="text" class="mt-1 block w-full"
                        :value="old('business_name', $dueDiligence?->business_name)" />
                </div>
                <div>
                    <x-label value="Business Nature" />
                    <x-input name="business_nature_text" type="text" class="mt-1 block w-full"
                        :value="old('business_nature_text', $dueDiligence?->business_nature_text)" />
                </div>
                <div>
                    <x-label value="Business Address" />
                    <x-input name="business_address" type="text" class="mt-1 block w-full"
                        :value="old('business_address', $dueDiligence?->business_address)" />
                </div>

                <div>
                    <x-label value="Type of Channels" />
                    <x-input name="type_of_channels" type="text" class="mt-1 block w-full"
                        :value="old('type_of_channels', $dueDiligence?->type_of_channels)" />
                </div>
                <div>
                    <x-label value="Type of Counterparties" />
                    <x-input name="type_of_counterparties" type="text" class="mt-1 block w-full"
                        :value="old('type_of_counterparties', $dueDiligence?->type_of_counterparties)" />
                </div>
                <div>
                    <x-label value="Geographies Involved" />
                    <x-input name="geographies_involved" type="text" class="mt-1 block w-full"
                        :value="old('geographies_involved', $dueDiligence?->geographies_involved)" />
                </div>

                <div>
                    <x-label value="Nature of Work" />
                    <x-input name="nature_of_work" type="text" class="mt-1 block w-full"
                        :value="old('nature_of_work', $dueDiligence?->nature_of_work)" />
                </div>
                <div>
                    <x-label value="Home Remittance Country" />
                    <select name="home_remittance_country_id"
                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}"
                                @selected(old('home_remittance_country_id', $dueDiligence?->home_remittance_country_id) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label value="Home Remittance Relationship" />
                    <x-input name="home_remittance_relationship" type="text" class="mt-1 block w-full"
                        :value="old('home_remittance_relationship', $dueDiligence?->home_remittance_relationship)" />
                </div>

                <div>
                    <x-label value="Monthly Income" />
                    <x-input name="monthly_income" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('monthly_income', $dueDiligence?->monthly_income)" />
                </div>
                <div>
                    <x-label value="Monthly Net Income (business)" />
                    <x-input name="monthly_net_income" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('monthly_net_income', $dueDiligence?->monthly_net_income)" />
                </div>
                {{-- Business CDD only: "Number of employees" (AOF-Ind p.15 / AOF-Ent p.17) --}}
                @if ($customer->needsOrganizationProfile())
                    <div>
                        <x-label value="Number of Employees" />
                        <x-input name="number_of_employees" type="number" min="0" class="mt-1 block w-full"
                            :value="old('number_of_employees', $dueDiligence?->number_of_employees)" />
                    </div>
                @endif

                <div>
                    <x-label value="Others (please specify)" />
                    <x-input name="source_of_income_other" type="text" class="mt-1 block w-full"
                        :value="old('source_of_income_other', $dueDiligence?->source_of_income_other)" />
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Source of Wealth" reference="AOF-Individual page 14">
            <div class="grid grid-cols-2 gap-2 md:grid-cols-5">
                @foreach ($wealthSources as $source)
                    <label class="flex items-start space-x-2 text-sm">
                        <input type="checkbox" name="wealth_sources[]" value="{{ $source->id }}"
                            @checked(in_array($source->id, (array) $selectedWealth, true))
                            class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>{{ $source->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input name="source_of_wealth_other" type="text" class="mt-3 block w-full md:w-1/3"
                placeholder="Others (please specify)" :value="old('source_of_wealth_other', $dueDiligence?->source_of_wealth_other)" />
        </x-aof-section>

        <x-aof-section title="Usual Mode of Transaction & Purpose of Account"
            reference="AOF — Usual Mode of Credit / Debit Transaction, Purpose of Account">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <p class="mb-2 text-sm font-semibold">Usual Mode of Credit Transaction</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($transactionModes as $mode)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="credit_modes[]" value="{{ $mode->id }}"
                                    @checked(in_array($mode->id, (array) $selectedCredit, true))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span>{{ $mode->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input name="credit_mode_other" type="text" class="mt-2 block w-full"
                        placeholder="Others (please specify)" :value="old('credit_mode_other', $dueDiligence?->credit_mode_other)" />
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold">Usual Mode of Debit Transaction</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($transactionModes as $mode)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="debit_modes[]" value="{{ $mode->id }}"
                                    @checked(in_array($mode->id, (array) $selectedDebit, true))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span>{{ $mode->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input name="debit_mode_other" type="text" class="mt-2 block w-full"
                        placeholder="Others (please specify)" :value="old('debit_mode_other', $dueDiligence?->debit_mode_other)" />
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold">Purpose of Account</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($accountPurposes as $purpose)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="account_purposes[]" value="{{ $purpose->id }}"
                                    @checked(in_array($purpose->id, (array) $selectedPurposes, true))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span>{{ $purpose->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error for="account_purposes" class="mt-1" />
                    <x-input name="purpose_of_account_other" type="text" class="mt-2 block w-full"
                        placeholder="Others (please specify)" :value="old('purpose_of_account_other', $dueDiligence?->purpose_of_account_other)" />
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold">Expected Type of Counter Parties</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($counterPartyTypes as $type)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="counter_party_types[]" value="{{ $type->id }}"
                                    @checked(in_array($type->id, (array) $selectedCounterParties, true))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span>{{ $type->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input name="counter_party_other" type="text" class="mt-2 block w-full"
                        placeholder="Others (please specify)" :value="old('counter_party_other', $dueDiligence?->counter_party_other)" />
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Expected Turnover & Ultimate Beneficial Owner"
            reference="For Bank Use Only — Expected Aggregate Credit / Debit, IFTT, OFTT, UBO">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <x-label value="Initial Deposit" />
                    <x-input name="initial_deposit" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('initial_deposit', $dueDiligence?->initial_deposit ?? $account->initial_deposit)" />
                </div>
                <div>
                    <x-label value="Expected Credit / month (amount)" />
                    <x-input name="expected_monthly_credit_amount" type="number" step="0.01" min="0"
                        class="mt-1 block w-full" :value="old('expected_monthly_credit_amount', $dueDiligence?->expected_monthly_credit_amount)" />
                </div>
                <div>
                    <x-label value="No. of credit transactions" />
                    <x-input name="expected_monthly_credit_count" type="number" min="0" class="mt-1 block w-full"
                        :value="old('expected_monthly_credit_count', $dueDiligence?->expected_monthly_credit_count)" />
                </div>
                <div>
                    <x-label value="Currency symbol (FCY only)" />
                    <x-input name="currency_symbol" type="text" class="mt-1 block w-full"
                        :value="old('currency_symbol', $dueDiligence?->currency_symbol)" />
                </div>
                <div>
                    <x-label value="Expected Debit / month (amount)" />
                    <x-input name="expected_monthly_debit_amount" type="number" step="0.01" min="0"
                        class="mt-1 block w-full" :value="old('expected_monthly_debit_amount', $dueDiligence?->expected_monthly_debit_amount)" />
                </div>
                <div>
                    <x-label value="No. of debit transactions" />
                    <x-input name="expected_monthly_debit_count" type="number" min="0" class="mt-1 block w-full"
                        :value="old('expected_monthly_debit_count', $dueDiligence?->expected_monthly_debit_count)" />
                </div>
                <div>
                    <x-label value="Expected IFTT amount / month" />
                    <x-input name="expected_iftt_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('expected_iftt_amount', $dueDiligence?->expected_iftt_amount)" />
                </div>
                <div>
                    <x-label value="Expected OFTT amount / month" />
                    <x-input name="expected_oftt_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('expected_oftt_amount', $dueDiligence?->expected_oftt_amount)" />
                </div>
            </div>

            <p class="mb-2 mt-5 text-sm font-semibold">Ultimate Beneficial Owner (if different from customer)</p>
            @foreach ($ubos as $index => $row)
                <div class="mb-2 grid grid-cols-1 gap-3 md:grid-cols-4">
                    <x-input name="ubos[{{ $index }}][name]" type="text" class="block w-full" placeholder="UBO name"
                        :value="$row['name'] ?? null" />
                    <select name="ubos[{{ $index }}][relationship_id]" class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Relationship with customer</option>
                        @foreach ($relationships->where('context_ubo', true) as $relationship)
                            <option value="{{ $relationship->id }}" @selected(($row['relationship_id'] ?? null) === $relationship->id)>
                                {{ $relationship->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="ubos[{{ $index }}][identification_document_type_id]"
                        class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">ID document type</option>
                        @foreach ($identificationDocumentTypes as $type)
                            <option value="{{ $type->id }}" @selected(($row['identification_document_type_id'] ?? null) === $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input name="ubos[{{ $index }}][identification_number]" type="text" class="block w-full"
                        placeholder="ID number" :value="$row['identification_number'] ?? null" />
                </div>
            @endforeach
        </x-aof-section>

        <x-aof-section title="Minimum Documentation to be Obtained"
            subtitle="Checklist generated from the customer category selected on the form."
            reference="AOF-Individual page 12 / AOF-Entity pages 12-14">
            @forelse ($documentRequirements as $requirement)
                @php $onFile = $documentsOnFile[$requirement->document_type_id] ?? null; @endphp
                <div class="mb-2 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 md:grid-cols-5 dark:border-gray-700">
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium">
                            {{ $requirement->documentType->name }}
                            @if ($requirement->is_mandatory)
                                <span class="ml-1 rounded bg-red-100 px-1 text-[10px] font-bold text-red-700">MANDATORY</span>
                            @endif
                        </p>
                        @if ($requirement->applies_when)
                            <p class="text-xs text-gray-400">{{ $requirement->applies_when }}</p>
                        @endif
                    </div>

                    <select name="documents[{{ $requirement->document_type_id }}][status]"
                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        @foreach (['pending', 'received', 'verified', 'waived', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(($onFile?->status ?? 'pending') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>

                    <div>
                        <input type="file" name="documents[{{ $requirement->document_type_id }}][file]"
                            class="block w-full text-xs">
                        @if ($onFile?->file_path)
                            <a class="text-xs text-blue-600 underline"
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($onFile->file_path) }}" target="_blank">
                                {{ $onFile->file_name ?? 'View file' }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center text-xs">
                            <input type="hidden" name="documents[{{ $requirement->document_type_id }}][is_attested]" value="0">
                            <input type="checkbox" name="documents[{{ $requirement->document_type_id }}][is_attested]"
                                value="1" @checked($onFile?->is_attested)
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2">Attested</span>
                        </label>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">
                    No documentation checklist is configured for this customer category yet.
                </p>
            @endforelse
        </x-aof-section>

        <x-aof-section title="Recommendation" reference="AOF last page — Sales Staff / Relationship Manager">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-input name="sales_staff_name_1" type="text" class="block w-full"
                    placeholder="Sales staff / RM (1)" :value="old('sales_staff_name_1', $accountOpeningRequest->sales_staff_name_1)" />
                <x-input name="sales_staff_employee_no_1" type="text" class="block w-full"
                    placeholder="Employee No. (1)" :value="old('sales_staff_employee_no_1', $accountOpeningRequest->sales_staff_employee_no_1)" />
                <x-input name="sales_staff_name_2" type="text" class="block w-full"
                    placeholder="Sales staff / RM (2)" :value="old('sales_staff_name_2', $accountOpeningRequest->sales_staff_name_2)" />
                <x-input name="sales_staff_employee_no_2" type="text" class="block w-full"
                    placeholder="Employee No. (2)" :value="old('sales_staff_employee_no_2', $accountOpeningRequest->sales_staff_employee_no_2)" />
            </div>
        </x-aof-section>
    @endif
</x-aof-step>
