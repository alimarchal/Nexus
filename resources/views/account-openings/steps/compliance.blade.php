@php
    $fatca = $customer->fatcaDetail;
    $isEntity = $customer->customer_type === \App\Models\Customer::TYPE_ENTITY;

    $taxResidencies = old('tax_residencies', $customer->taxResidencies->map(fn ($row) => [
        'country_id' => $row->country_id,
        'tin' => $row->tin,
        'no_tin_reason' => $row->no_tin_reason,
        'reason_b_explanation' => $row->reason_b_explanation,
    ])->all());

    while (count($taxResidencies) < 3) {
        $taxResidencies[] = [];
    }

    $controllingPersons = old('controlling_persons', $customer->controllingPersons->map(fn ($row) => [
        'person_name' => $row->person_name,
        'designation' => $row->designation,
        'shareholding_percentage' => $row->shareholding_percentage,
        'controlling_person_type_id' => $row->controlling_person_type_id,
        'declaration_basis' => $row->declaration_basis,
    ])->all());

    while (count($controllingPersons) < 5) {
        $controllingPersons[] = [];
    }

    $yesNo = function (string $name, ?bool $current, string $label) {
        return compact('name', 'current', 'label');
    };
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="FATCA / CRS & Tax Residency">

    @unless ($isEntity)
        <x-aof-section title="FATCA — Individual / Sole Proprietor / Joint Holder / Minor & Mandate Holder"
            reference="AOF-Individual page 3 — Questions 1 to 4">
            <div class="space-y-3">
                @foreach ([
                    'q1_is_us_person' => 'Are you a US National / Citizen / Green Card Holder or U.S. Resident?',
                    'q2_country_of_birth_us' => 'Is your country of birth US?',
                    'q3_has_us_address_or_phone' => 'Do you have a U.S. Address or Telephone No?',
                    'q4_has_us_mandate_or_links' => 'Have you assigned a mandate to a person having an address in the US, or any other US links?',
                ] as $field => $question)
                    <label class="flex items-start justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $question }}</span>
                        <span class="ml-4 shrink-0">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                @checked(old($field, $fatca?->{$field}))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-1 text-xs font-semibold">Yes</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap gap-6 text-sm">
                <label class="inline-flex items-center">
                    <input type="hidden" name="form_w9_signed" value="0">
                    <input type="checkbox" name="form_w9_signed" value="1" @checked(old('form_w9_signed', $fatca?->form_w9_signed))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Form W9 signed</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="hidden" name="form_w8_ben_signed" value="0">
                    <input type="checkbox" name="form_w8_ben_signed" value="1" @checked(old('form_w8_ben_signed', $fatca?->form_w8_ben_signed))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Form W-8 BEN signed</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="hidden" name="us_nationality_revoked" value="0">
                    <input type="checkbox" name="us_nationality_revoked" value="1" @checked(old('us_nationality_revoked', $fatca?->us_nationality_revoked))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2">Claims revocation of U.S. nationality</span>
                </label>
            </div>
        </x-aof-section>
    @endunless

    @if ($isEntity)
        <x-aof-section title="FATCA For Non-Financial Entities"
            reference="AOF-Entity page 3 — Country of Incorporation, Active / Passive NFE">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-3">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="entity_incorporated_in_us" value="0">
                        <input type="checkbox" name="entity_incorporated_in_us" value="1"
                            @checked(old('entity_incorporated_in_us', $fatca?->entity_incorporated_in_us))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm">
                            The Country of Incorporation and/or Parent Company Country of Incorporation is the U.S.
                        </span>
                    </label>
                </div>

                <div>
                    <x-label value="Country of Incorporation (CoI)" />
                    <select name="incorporation_country_id" class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('incorporation_country_id', $fatca?->incorporation_country_id) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label value="Entity Type" />
                    <select name="nfe_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        <option value="active" @selected(old('nfe_type', $fatca?->nfe_type) === 'active')>Active Non-Financial Entity</option>
                        <option value="passive" @selected(old('nfe_type', $fatca?->nfe_type) === 'passive')>Passive Non-Financial Entity</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="form_w8_ben_e_signed" value="0">
                        <input type="checkbox" name="form_w8_ben_e_signed" value="1"
                            @checked(old('form_w8_ben_e_signed', $fatca?->form_w8_ben_e_signed))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm">W8-BEN-E signed</span>
                    </label>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="CRS — Entity Classification"
            subtitle="Select only one option from the list."
            reference="AOF-Entity pages 3 & 4 — options (a) to (i)">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-3">
                    <select name="crs_entity_classification_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($crsClassifications as $classification)
                            <option value="{{ $classification->id }}"
                                @selected(old('crs_entity_classification_id', $fatca?->crs_entity_classification_id) === $classification->id)>
                                ({{ $classification->form_option }}) {{ $classification->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label value="GIIN (if obtained)" />
                    <x-input name="giin" type="text" class="mt-1 block w-full" :value="old('giin', $fatca?->giin)" />
                </div>

                <div class="md:col-span-2">
                    <x-label value="Listed stock exchange (for a listed Active NFE)" />
                    <x-input name="listed_stock_exchange" type="text" class="mt-1 block w-full"
                        :value="old('listed_stock_exchange', $fatca?->listed_stock_exchange)" />
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Controlling Persons"
            subtitle="Individuals holding 10% or more shares / voting rights (FATCA) and 20% or more (CRS)."
            reference="AOF-Entity page 3 — Applicant 1 to 10">
            @foreach ($controllingPersons as $index => $row)
                <div class="mb-2 grid grid-cols-1 gap-3 md:grid-cols-5">
                    <div class="md:col-span-2">
                        <x-input name="controlling_persons[{{ $index }}][person_name]" type="text" class="block w-full"
                            placeholder="Applicant {{ $index + 1 }} name" :value="$row['person_name'] ?? null" />
                    </div>
                    <x-input name="controlling_persons[{{ $index }}][designation]" type="text" class="block w-full"
                        placeholder="Designation" :value="$row['designation'] ?? null" />
                    <x-input name="controlling_persons[{{ $index }}][shareholding_percentage]" type="number" step="0.01"
                        min="0" max="100" class="block w-full" placeholder="Share / voting %"
                        :value="$row['shareholding_percentage'] ?? null" />
                    <select name="controlling_persons[{{ $index }}][controlling_person_type_id]"
                        class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Type of controlling person</option>
                        @foreach ($controllingPersonTypes as $type)
                            <option value="{{ $type->id }}" @selected(($row['controlling_person_type_id'] ?? null) === $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </x-aof-section>
    @endif

    <x-aof-section title="Tax Residency Status"
        subtitle="Reason A: the country does not issue TINs. Reason B: unable to obtain one (explanation required). Reason C: no TIN is required."
        reference="AOF-Individual page 3 / AOF-Entity page 4">
        <label class="mb-3 inline-flex items-center">
            <input type="hidden" name="is_tax_resident_other_country" value="0">
            <input type="checkbox" name="is_tax_resident_other_country" value="1"
                @checked(old('is_tax_resident_other_country', $fatca?->is_tax_resident_other_country))
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ml-2 text-sm">Tax resident of a country other than Pakistan &amp; USA</span>
        </label>

        @foreach ($taxResidencies as $index => $row)
            <div class="mb-2 grid grid-cols-1 gap-3 md:grid-cols-4">
                <select name="tax_residencies[{{ $index }}][country_id]"
                    class="select2 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Country / jurisdiction of tax residence</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected(($row['country_id'] ?? null) === $country->id)>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>

                <x-input name="tax_residencies[{{ $index }}][tin]" type="text" class="block w-full"
                    placeholder="TIN / NTN" :value="$row['tin'] ?? null" />

                <select name="tax_residencies[{{ $index }}][no_tin_reason]"
                    class="block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">If no TIN, reason</option>
                    @foreach (['A', 'B', 'C'] as $reason)
                        <option value="{{ $reason }}" @selected(($row['no_tin_reason'] ?? null) === $reason)>Reason {{ $reason }}</option>
                    @endforeach
                </select>

                <x-input name="tax_residencies[{{ $index }}][reason_b_explanation]" type="text" class="block w-full"
                    placeholder="Explanation (Reason B only)" :value="$row['reason_b_explanation'] ?? null" />
            </div>
        @endforeach

        <div class="mt-3 md:w-1/3">
            <x-label value="Self-certification date" />
            <x-input name="self_certification_date" type="date" class="mt-1 block w-full"
                :value="old('self_certification_date', $fatca?->self_certification_date?->toDateString())" />
        </div>
    </x-aof-section>

    <x-aof-section title="Politically Exposed Person" reference="For Bank Use Only — PEP / Political Connections">
        <div class="flex flex-wrap gap-6 text-sm">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_pep" value="0">
                <input type="checkbox" name="is_pep" value="1" @checked(old('is_pep', $customer->is_pep))
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">Customer is a Politically Exposed Person</span>
            </label>
            <label class="inline-flex items-center">
                <input type="hidden" name="pep_form_attached" value="0">
                <input type="checkbox" name="pep_form_attached" value="1"
                    @checked(old('pep_form_attached', $customer->pep_form_attached))
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2">PEP declaration form attached and approved by Head Office</span>
            </label>
        </div>
    </x-aof-section>
</x-aof-step>
