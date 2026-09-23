@php
    $identifications = old('identifications', $customer->identifications->map(fn ($row) => [
        'identification_document_type_id' => $row->identification_document_type_id,
        'document_number' => $row->document_number,
        'issue_date' => $row->issue_date?->toDateString(),
        'expiry_date' => $row->expiry_date?->toDateString(),
        'place_of_issuance' => $row->place_of_issuance,
        'nadra_token_number' => $row->nadra_token_number,
        'is_expired_accepted' => $row->is_expired_accepted,
        'verisys_verified' => $row->verisys_verified,
        'is_attested' => $row->is_attested,
    ])->all());

    if (count($identifications) === 0) {
        $identifications = [[]];
    }

    $isEntity = $customer->customer_type === \App\Models\Customer::TYPE_ENTITY;
    $primaryAddressType = $isEntity ? 'registered_business' : 'permanent_residential';
    $currentAddressType = $isEntity ? 'current_business' : 'current_residential';

    $addressTypes = [
        $primaryAddressType => $isEntity
            ? 'Registered Business Address'
            : 'Permanent Residential / Registered Business Address',
        $currentAddressType => $isEntity
            ? 'Current Business Address'
            : 'Current Residential Address',
        'mailing' => 'Account Statement / Mailing Address',
    ];

    $addressValue = fn (string $type, string $field) => old(
        "addresses.$type.$field",
        $customer->addresses->firstWhere('address_type', $type)?->{$field}
    );

    // The printed form numbers its telephone boxes (1) and (2); `sequence` on
    // customer_contacts is what keeps them apart.
    $contactValue = fn (string $type, int $sequence, string $field) => old(
        "contacts.$type.$sequence.$field",
        $customer->contacts
            ->first(fn ($row) => $row->contact_type === $type && (int) $row->sequence === $sequence)?->{$field}
    );

    $nextOfKin = old('next_of_kin', $customer->nextOfKin->map(fn ($row) => [
        'name' => $row->name,
        'relationship_id' => $row->relationship_id,
        'identification_number' => $row->identification_number,
        'address' => $row->address,
        'tehsil_district' => $row->tehsil_district,
        'nearest_landmark' => $row->nearest_landmark,
        'city' => $row->city,
        'country_id' => $row->country_id,
        'postal_code' => $row->postal_code,
        'telephone' => $row->telephone,
        'email' => $row->email,
    ])->all());

    if (count($nextOfKin) === 0) {
        $nextOfKin = [[]];
    }
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="Identification, Address & Contact">

    <x-aof-section title="Identification"
        reference="AOF page 2 — CNIC / NICOP / POC / ARC / PoR / Passport, Issued & Expiry Date">
        <div x-data="{ rows: {{ count($identifications) }} }">
            <template x-for="index in rows" :key="index"></template>

            @foreach ($identifications as $index => $row)
                <div class="mb-3 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 md:grid-cols-6 dark:border-gray-700">
                    <div class="md:col-span-2">
                        <x-label value="Document Type" />
                        <select name="identifications[{{ $index }}][identification_document_type_id]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-</option>
                            @foreach ($identificationDocumentTypes as $type)
                                <option value="{{ $type->id }}" @selected(($row['identification_document_type_id'] ?? null) === $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label value="Document Number" />
                        <x-input name="identifications[{{ $index }}][document_number]" type="text"
                            class="mt-1 block w-full" :value="$row['document_number'] ?? null" />
                    </div>

                    <div>
                        <x-label value="Issued Date" />
                        <x-input name="identifications[{{ $index }}][issue_date]" type="date" class="mt-1 block w-full"
                            :value="$row['issue_date'] ?? null" />
                    </div>

                    <div>
                        <x-label value="Expiry Date" />
                        <x-input name="identifications[{{ $index }}][expiry_date]" type="date" class="mt-1 block w-full"
                            :value="$row['expiry_date'] ?? null" />
                    </div>

                    <div>
                        <x-label value="Place of Issuance" />
                        <x-input name="identifications[{{ $index }}][place_of_issuance]" type="text"
                            class="mt-1 block w-full" :value="$row['place_of_issuance'] ?? null" />
                    </div>

                    <div class="md:col-span-6">
                        <div class="flex flex-wrap gap-6 text-sm">
                            <label class="inline-flex items-center">
                                <input type="hidden" name="identifications[{{ $index }}][is_attested]" value="0">
                                <input type="checkbox" name="identifications[{{ $index }}][is_attested]" value="1"
                                    @checked($row['is_attested'] ?? false)
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">Attested copy on file</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="hidden" name="identifications[{{ $index }}][verisys_verified]" value="0">
                                <input type="checkbox" name="identifications[{{ $index }}][verisys_verified]" value="1"
                                    @checked($row['verisys_verified'] ?? false)
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">NADRA Verisys done</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="hidden" name="identifications[{{ $index }}][is_expired_accepted]" value="0">
                                <input type="checkbox" name="identifications[{{ $index }}][is_expired_accepted]" value="1"
                                    @checked($row['is_expired_accepted'] ?? false)
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">Expired CNIC accepted on NADRA token</span>
                            </label>

                            <x-input name="identifications[{{ $index }}][nadra_token_number]" type="text"
                                class="block w-48" placeholder="NADRA token no."
                                :value="$row['nadra_token_number'] ?? null" />
                        </div>
                    </div>
                </div>
            @endforeach

            @for ($extra = count($identifications); $extra < count($identifications) + 2; $extra++)
                <div class="mb-3 grid grid-cols-1 gap-3 rounded-lg border border-dashed border-gray-200 p-3 md:grid-cols-6 dark:border-gray-700">
                    <div class="md:col-span-2">
                        <x-label value="Additional Document Type" />
                        <select name="identifications[{{ $extra }}][identification_document_type_id]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-</option>
                            @foreach ($identificationDocumentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label value="Document Number" />
                        <x-input name="identifications[{{ $extra }}][document_number]" type="text" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label value="Issued Date" />
                        <x-input name="identifications[{{ $extra }}][issue_date]" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label value="Expiry Date" />
                        <x-input name="identifications[{{ $extra }}][expiry_date]" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label value="Place of Issuance" />
                        <x-input name="identifications[{{ $extra }}][place_of_issuance]" type="text" class="mt-1 block w-full" />
                    </div>
                </div>
            @endfor
        </div>
    </x-aof-section>

    <x-aof-section title="Contact Details — Addresses"
        subtitle="Do not use a PO Box or in-care-of address."
        reference="AOF page 2 & 4 — Permanent / Registered, Current and Mailing address">
        @foreach ($addressTypes as $type => $label)
            <div class="mb-4">
                <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <x-label value="House / Office No." />
                        <x-input name="addresses[{{ $type }}][house_office_no]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'house_office_no')" />
                    </div>
                    <div>
                        <x-label value="Street / Area" />
                        <x-input name="addresses[{{ $type }}][street_area]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'street_area')" />
                    </div>
                    <div>
                        <x-label value="Tehsil / District" />
                        <x-input name="addresses[{{ $type }}][tehsil_district]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'tehsil_district')" />
                    </div>
                    <div>
                        <x-label value="Nearest Landmark" />
                        <x-input name="addresses[{{ $type }}][nearest_landmark]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'nearest_landmark')" />
                    </div>
                    <div>
                        <x-label value="City" />
                        <x-input name="addresses[{{ $type }}][city]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'city')" />
                    </div>
                    <div>
                        <x-label value="Country" />
                        <select name="addresses[{{ $type }}][country_id]"
                            class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected($addressValue($type, 'country_id') === $country->id)>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label value="Postal Code" />
                        <x-input name="addresses[{{ $type }}][postal_code]" type="text" class="mt-1 block w-full"
                            :value="$addressValue($type, 'postal_code')" />
                    </div>
                </div>
            </div>
        @endforeach
    </x-aof-section>

    <x-aof-section title="Contact Details — Phone, Email, Fax"
        subtitle="Country code is mandatory with mobile and telephone numbers. Mobile is mandatory for Mobile Banking, e-mail for Internet Banking and e-Statement."
        reference="AOF page 3 (Individual) / page 2 (Entity)">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <x-label value="***Mobile No." />
                <div class="mt-1 flex space-x-2">
                    <x-input name="contacts[mobile][1][country_code]" type="text" class="block w-24" placeholder="+92"
                        :value="$contactValue('mobile', 1, 'country_code') ?? '+92'" />
                    <x-input name="contacts[mobile][1][value]" type="text" class="block w-full"
                        :value="$contactValue('mobile', 1, 'value')" />
                </div>
                <x-input-error for="contacts.mobile.1.value" class="mt-1" />
            </div>

            <div>
                <x-label value="Telephone No. Residential / Office (1)" />
                <div class="mt-1 flex space-x-2">
                    <x-input name="contacts[telephone_residence_office][1][country_code]" type="text" class="block w-24"
                        placeholder="+92" :value="$contactValue('telephone_residence_office', 1, 'country_code')" />
                    <x-input name="contacts[telephone_residence_office][1][value]" type="text" class="block w-full"
                        :value="$contactValue('telephone_residence_office', 1, 'value')" />
                </div>
            </div>

            <div>
                <x-label value="Telephone No. Residential / Office (2)" />
                <div class="mt-1 flex space-x-2">
                    <x-input name="contacts[telephone_residence_office][2][country_code]" type="text" class="block w-24"
                        placeholder="+92" :value="$contactValue('telephone_residence_office', 2, 'country_code')" />
                    <x-input name="contacts[telephone_residence_office][2][value]" type="text" class="block w-full"
                        :value="$contactValue('telephone_residence_office', 2, 'value')" />
                </div>
            </div>

            <div>
                <x-label value="**Personal / Office E-Mail ID" />
                <x-input name="contacts[email_personal_office][1][value]" type="email" class="mt-1 block w-full"
                    :value="$contactValue('email_personal_office', 1, 'value')" />
                <x-input-error for="contacts.email_personal_office.1.value" class="mt-1" />
            </div>

            <div>
                <x-label value="Personal / Office Fax" />
                <x-input name="contacts[fax_personal_office][1][value]" type="text" class="mt-1 block w-full"
                    :value="$contactValue('fax_personal_office', 1, 'value')" />
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Zakat Exemption"
        reference="AOF page 3 — Zakat is applicable in PKR Saving Accounts only">
        <div x-data="{ exempt: {{ old('is_zakat_exempt', $customer->is_zakat_exempt) ? 'true' : 'false' }} }">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_zakat_exempt" value="0">
                <input type="checkbox" name="is_zakat_exempt" value="1" x-model="exempt"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Customer claims Zakat exemption</span>
            </label>

            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3" x-show="exempt" x-cloak>
                <div>
                    <x-label value="Exemption Code" />
                    <select name="zakat_exemption_reason_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($zakatExemptionReasons as $reason)
                            <option value="{{ $reason->id }}" @selected(old('zakat_exemption_reason_id', $customer->zakat_exemption_reason_id) === $reason->id)>
                                {{ $reason->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error for="zakat_exemption_reason_id" class="mt-1" />
                </div>
                <div>
                    <x-label value="Others (please specify)" />
                    <x-input name="zakat_exemption_other" type="text" class="mt-1 block w-full"
                        :value="old('zakat_exemption_other', $customer->zakat_exemption_other)" />
                </div>
                <div>
                    <x-label value="CZ-50 Declaration Submitted On" />
                    <x-input name="cz50_submitted_on" type="date" class="mt-1 block w-full"
                        :value="old('cz50_submitted_on', $customer->cz50_submitted_on?->toDateString())" />
                </div>
            </div>
        </div>
    </x-aof-section>

    <x-aof-section title="Contact Persons (Next of Kin)"
        subtitle="Next of kin to be contacted for ascertaining the customer's whereabouts."
        reference="AOF-Individual page 14">
        @foreach ($nextOfKin as $index => $row)
            <div class="mb-3 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 md:grid-cols-4 dark:border-gray-700">
                <div>
                    <x-label value="Name" />
                    <x-input name="next_of_kin[{{ $index }}][name]" type="text" class="mt-1 block w-full"
                        :value="$row['name'] ?? null" />
                </div>
                <div>
                    <x-label value="Relationship" />
                    <select name="next_of_kin[{{ $index }}][relationship_id]"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($relationships->where('context_next_of_kin', true) as $relationship)
                            <option value="{{ $relationship->id }}" @selected(($row['relationship_id'] ?? null) === $relationship->id)>
                                {{ $relationship->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label value="CNIC / NICOP / POC No." />
                    <x-input name="next_of_kin[{{ $index }}][identification_number]" type="text"
                        class="mt-1 block w-full" :value="$row['identification_number'] ?? null" />
                </div>
                <div>
                    <x-label value="Telephone" />
                    <x-input name="next_of_kin[{{ $index }}][telephone]" type="text" class="mt-1 block w-full"
                        :value="$row['telephone'] ?? null" />
                </div>
                <div class="md:col-span-2">
                    <x-label value="Address" />
                    <x-input name="next_of_kin[{{ $index }}][address]" type="text" class="mt-1 block w-full"
                        :value="$row['address'] ?? null" />
                </div>
                <div>
                    <x-label value="Tehsil / District" />
                    <x-input name="next_of_kin[{{ $index }}][tehsil_district]" type="text" class="mt-1 block w-full"
                        :value="$row['tehsil_district'] ?? null" />
                </div>
                <div>
                    <x-label value="Nearest Landmark" />
                    <x-input name="next_of_kin[{{ $index }}][nearest_landmark]" type="text" class="mt-1 block w-full"
                        :value="$row['nearest_landmark'] ?? null" />
                </div>
                <div>
                    <x-label value="City" />
                    <x-input name="next_of_kin[{{ $index }}][city]" type="text" class="mt-1 block w-full"
                        :value="$row['city'] ?? null" />
                </div>
                <div>
                    <x-label value="Country" />
                    <select name="next_of_kin[{{ $index }}][country_id]"
                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(($row['country_id'] ?? null) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label value="Post Code" />
                    <x-input name="next_of_kin[{{ $index }}][postal_code]" type="text" class="mt-1 block w-full"
                        :value="$row['postal_code'] ?? null" />
                </div>
                <div>
                    <x-label value="Email" />
                    <x-input name="next_of_kin[{{ $index }}][email]" type="email" class="mt-1 block w-full"
                        :value="$row['email'] ?? null" />
                </div>
            </div>
        @endforeach
    </x-aof-section>
</x-aof-step>
