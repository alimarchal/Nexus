@php
    $individual = $customer->individual;
    $organization = $customer->organization;
    $selectedNationalities = old('nationalities', $customer->nationalities->sortBy('sequence')->pluck('country_id')->all());
    $selectedSpecial = old('special_categories', $customer->specialCategories->pluck('id')->all());
@endphp

<x-aof-step :accountOpeningRequest="$accountOpeningRequest" :step="$step" :progress="$progress"
    title="Customer Information Form (CIF)" enctype="multipart/form-data">

    <x-aof-section title="Category" reference="CIF page — Customer Category / Economic Sector Code">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-label value="Customer Category" />
                <x-input type="text" class="mt-1 block w-full bg-gray-100" :value="$customer->customerCategory?->name" disabled />
            </div>
            <div>
                <x-label for="category_code_description" value="Category Code & Desc." />
                <x-input id="category_code_description" name="category_code_description" type="text"
                    class="mt-1 block w-full" :value="old('category_code_description', $customer->category_code_description)" />
            </div>
            <div>
                <x-label for="customer_category_other" value="Others (please specify)" />
                <x-input id="customer_category_other" name="customer_category_other" type="text"
                    class="mt-1 block w-full" :value="old('customer_category_other', $customer->customer_category_other)" />
            </div>
        </div>
    </x-aof-section>

    @if ($customer->needsIndividualProfile())
        <x-aof-section title="Customer Details For Individuals"
            reference="AOF-Individual page 2 — Name, parentage, gender, DOB, profession">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <x-label for="title" value="Mr / Mrs / Ms" />
                    <select id="title" name="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach (['Mr', 'Mrs', 'Ms'] as $option)
                            <option value="{{ $option }}" @selected(old('title', $individual?->title) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-label for="full_name" value="*Name (as per CNIC)" />
                    <x-input id="full_name" name="full_name" type="text" class="mt-1 block w-full"
                        :value="old('full_name', $individual?->full_name)" />
                    <x-input-error for="full_name" class="mt-1" />
                </div>

                <div>
                    <x-label for="parentage_relationship_id" value="*S/o, D/o, W/o" />
                    <select id="parentage_relationship_id" name="parentage_relationship_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($relationships->where('context_parentage', true) as $relationship)
                            <option value="{{ $relationship->id }}"
                                @selected(old('parentage_relationship_id', $individual?->parentage_relationship_id) === $relationship->id)>
                                {{ $relationship->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-label for="parent_or_spouse_name" value="Father / Husband Name" />
                    <x-input id="parent_or_spouse_name" name="parent_or_spouse_name" type="text"
                        class="mt-1 block w-full" :value="old('parent_or_spouse_name', $individual?->parent_or_spouse_name)" />
                </div>

                <div>
                    <x-label for="mother_maiden_name" value="*Mother's Maiden Name" />
                    <x-input id="mother_maiden_name" name="mother_maiden_name" type="text" class="mt-1 block w-full"
                        :value="old('mother_maiden_name', $individual?->mother_maiden_name)" />
                </div>

                <div>
                    <x-label for="gender_id" value="*Gender" />
                    <select id="gender_id" name="gender_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($genders as $gender)
                            <option value="{{ $gender->id }}" @selected(old('gender_id', $individual?->gender_id) === $gender->id)>
                                {{ $gender->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="marital_status_id" value="*Marital Status" />
                    <select id="marital_status_id" name="marital_status_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($maritalStatuses as $status)
                            <option value="{{ $status->id }}" @selected(old('marital_status_id', $individual?->marital_status_id) === $status->id)>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="marital_status_other" value="Marital Status — Others (please specify)" />
                    <x-input id="marital_status_other" name="marital_status_other" type="text" class="mt-1 block w-full"
                        :value="old('marital_status_other', $individual?->marital_status_other)" />
                </div>

                <div>
                    <x-label for="education_level_id" value="Education" />
                    <select id="education_level_id" name="education_level_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($educationLevels as $level)
                            <option value="{{ $level->id }}" @selected(old('education_level_id', $individual?->education_level_id) === $level->id)>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="education_other" value="Education — Others (please specify)" />
                    <x-input id="education_other" name="education_other" type="text" class="mt-1 block w-full"
                        :value="old('education_other', $individual?->education_other)" />
                </div>

                <div>
                    <x-label for="date_of_birth" value="*Date of Birth" />
                    <x-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full"
                        :value="old('date_of_birth', $individual?->date_of_birth?->toDateString())" />
                    <x-input-error for="date_of_birth" class="mt-1" />
                </div>

                <div>
                    <x-label for="birth_country_id" value="Country of Birth" />
                    <select id="birth_country_id" name="birth_country_id"
                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('birth_country_id', $individual?->birth_country_id) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="birth_city" value="City of Birth" />
                    <x-input id="birth_city" name="birth_city" type="text" class="mt-1 block w-full"
                        :value="old('birth_city', $individual?->birth_city)" />
                </div>

                <div>
                    <x-label for="residence_country_id" value="*Country of Residence" />
                    <select id="residence_country_id" name="residence_country_id"
                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('residence_country_id', $individual?->residence_country_id) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="profession_id" value="*Profession" />
                    <select id="profession_id" name="profession_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($professions as $profession)
                            <option value="{{ $profession->id }}" @selected(old('profession_id', $individual?->profession_id) === $profession->id)>
                                {{ $profession->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="profession_other" value="Profession — Others (please specify)" />
                    <x-input id="profession_other" name="profession_other" type="text" class="mt-1 block w-full"
                        :value="old('profession_other', $individual?->profession_other)" />
                </div>

                <div>
                    <x-label for="designation" value="Designation" />
                    <x-input id="designation" name="designation" type="text" class="mt-1 block w-full"
                        :value="old('designation', $individual?->designation)" />
                </div>

                <div>
                    <x-label for="ntn" value="N.T.N Number" />
                    <x-input id="ntn" name="ntn" type="text" class="mt-1 block w-full"
                        :value="old('ntn', $individual?->ntn)" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="employer_institution_name" value="Employer's / Educational Institution Name" />
                    <x-input id="employer_institution_name" name="employer_institution_name" type="text"
                        class="mt-1 block w-full" :value="old('employer_institution_name', $individual?->employer_institution_name)" />
                </div>

                <div class="md:col-span-3">
                    <x-label for="employer_institution_address" value="Employer's / Educational Institution Address" />
                    <textarea id="employer_institution_address" name="employer_institution_address" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('employer_institution_address', $individual?->employer_institution_address) }}</textarea>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Nationalities" subtitle="Disclose all nationalities held (up to three)."
            reference="AOF-Individual page 2 — *Nationality (1) / Other Nationalities (2)(3)">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @for ($i = 0; $i < 3; $i++)
                    <div>
                        <x-label :value="$i === 0 ? '*Nationality (1)' : 'Other Nationality (' . ($i + 1) . ')'" />
                        <select name="nationalities[{{ $i }}]"
                            class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected(($selectedNationalities[$i] ?? null) === $country->id)>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endfor
            </div>
        </x-aof-section>

        <x-aof-section title="Details for Minor Account Holders"
            reference="AOF-Individual page 2 — Name of Guardian / Relationship with Guardian">
            <div x-data="{ isMinor: {{ old('is_minor', $individual?->is_minor) ? 'true' : 'false' }} }">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_minor" value="0">
                    <input type="checkbox" name="is_minor" value="1" x-model="isMinor"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This account is for a minor</span>
                </label>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" x-show="isMinor" x-cloak>
                    <div>
                        <x-label for="guardian_name" value="*Name of Guardian" />
                        <x-input id="guardian_name" name="guardian_name" type="text" class="mt-1 block w-full"
                            :value="old('guardian_name', $individual?->guardian_name)" />
                        <x-input-error for="guardian_name" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="guardian_relationship_id" value="Relationship with Guardian / Minor" />
                        <select id="guardian_relationship_id" name="guardian_relationship_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-</option>
                            @foreach ($relationships->where('context_guardian', true) as $relationship)
                                <option value="{{ $relationship->id }}"
                                    @selected(old('guardian_relationship_id', $individual?->guardian_relationship_id) === $relationship->id)>
                                    {{ $relationship->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Mobile Number Portability & Witness"
            reference="AOF-Individual page 3 — MNP / For Visually Impaired Persons & Blind Customers Only">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="inline-flex items-center">
                        <input type="hidden" name="has_availed_mnp" value="0">
                        <input type="checkbox" name="has_availed_mnp" value="1"
                            @checked(old('has_availed_mnp', $individual?->has_availed_mnp))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has availed MNP services</span>
                    </label>
                    <x-input name="mnp_new_provider" type="text" class="mt-2 block w-full"
                        placeholder="New mobile service provider"
                        :value="old('mnp_new_provider', $individual?->mnp_new_provider)" />
                </div>

                <div x-data="{ witness: {{ old('requires_witness', $individual?->requires_witness) ? 'true' : 'false' }} }">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="requires_witness" value="0">
                        <input type="checkbox" name="requires_witness" value="1" x-model="witness"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Witness required (illiterate / visually impaired customer)
                        </span>
                    </label>

                    <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-3" x-show="witness" x-cloak>
                        <x-input name="witness_name" type="text" placeholder="Name of witness" class="block w-full"
                            :value="old('witness_name', $individual?->witness_name)" />
                        <x-input name="witness_relation" type="text" placeholder="Relation with customer"
                            class="block w-full" :value="old('witness_relation', $individual?->witness_relation)" />
                        <x-input name="witness_cnic" type="text" placeholder="Witness CNIC" class="block w-full"
                            :value="old('witness_cnic', $individual?->witness_cnic)" />
                        <div class="md:col-span-3">
                            <x-label value="Witness signature (image)" />
                            <input type="file" name="witness_signature" accept="image/*" class="mt-1 block w-full text-xs">
                            @if ($individual?->witness_signature_path)
                                <a class="text-xs text-blue-600 underline" target="_blank"
                                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($individual->witness_signature_path) }}">View current</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Photograph"
            subtitle="Required for a Photo Account, and where the customer's signature is shaky or immature."
            reference="AOF Important Notes (c) and (e)">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-label value="Passport sized photograph" />
                    <input type="file" name="photograph" accept="image/*" class="mt-1 block w-full text-xs">
                    @if ($individual?->photograph_path)
                        <a class="text-xs text-blue-600 underline" target="_blank"
                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($individual->photograph_path) }}">View current</a>
                    @endif
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center text-sm">
                        <input type="hidden" name="thumb_impression_taken" value="0">
                        <input type="checkbox" name="thumb_impression_taken" value="1"
                            @checked(old('thumb_impression_taken', $individual?->thumb_impression_taken))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2">Left &amp; right thumb impressions taken on the SS Card</span>
                    </label>
                </div>
            </div>
        </x-aof-section>

        <x-aof-section title="Special Category of Account"
            reference="AOF-Individual page 2 — Special Category (if any)">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($specialCategories as $special)
                    <label class="flex items-start space-x-2 text-sm">
                        <input type="checkbox" name="special_categories[]" value="{{ $special->id }}"
                            @checked(in_array($special->id, (array) $selectedSpecial, true))
                            class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>
                            {{ $special->name }}
                            @if ($special->requires_extra_value)
                                <x-input name="special_category_values[{{ $special->id }}]" type="text"
                                    class="mt-1 block w-full text-xs" placeholder="{{ $special->extra_value_label }}"
                                    :value="old('special_category_values.' . $special->id, $customer->specialCategories->firstWhere('id', $special->id)?->pivot?->extra_value)" />
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </x-aof-section>
    @endif

    @if ($customer->needsOrganizationProfile())
        <x-aof-section title="Customer Details For Business"
            reference="AOF-Entity page 2 — Company/Business Name, Nature of Business, Registration">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <x-label for="business_name" value="*Company / Business Name" />
                    <x-input id="business_name" name="business_name" type="text" class="mt-1 block w-full"
                        :value="old('business_name', $organization?->business_name)" />
                    <x-input-error for="business_name" class="mt-1" />
                </div>

                <div>
                    <x-label for="business_nature_id" value="*Nature of Business" />
                    <select id="business_nature_id" name="business_nature_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($businessNatures as $nature)
                            <option value="{{ $nature->id }}" @selected(old('business_nature_id', $organization?->business_nature_id) === $nature->id)>
                                {{ $nature->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="business_registration_number" value="Business Registration No." />
                    <x-input id="business_registration_number" name="business_registration_number" type="text"
                        class="mt-1 block w-full" :value="old('business_registration_number', $organization?->business_registration_number)" />
                </div>

                <div>
                    <x-label for="issued_date" value="Issued Date" />
                    <x-input id="issued_date" name="issued_date" type="date" class="mt-1 block w-full"
                        :value="old('issued_date', $organization?->issued_date?->toDateString())" />
                </div>

                <div>
                    <x-label for="expiry_date" value="Expiry Date" />
                    <x-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full"
                        :value="old('expiry_date', $organization?->expiry_date?->toDateString())" />
                </div>

                <div>
                    <x-label for="business_commencement_date" value="Business Commencement Date" />
                    <x-input id="business_commencement_date" name="business_commencement_date" type="date"
                        class="mt-1 block w-full" :value="old('business_commencement_date', $organization?->business_commencement_date?->toDateString())" />
                </div>

                <div>
                    <x-label for="business_incorporation_date" value="Business Incorporation Date" />
                    <x-input id="business_incorporation_date" name="business_incorporation_date" type="date"
                        class="mt-1 block w-full" :value="old('business_incorporation_date', $organization?->business_incorporation_date?->toDateString())" />
                </div>

                <div>
                    <x-label for="years_in_business" value="Years in Business" />
                    <x-input id="years_in_business" name="years_in_business" type="number" min="0"
                        class="mt-1 block w-full" :value="old('years_in_business', $organization?->years_in_business)" />
                </div>

                <div>
                    <x-label for="incorporation_country_id" value="Country of Incorporation" />
                    <select id="incorporation_country_id" name="incorporation_country_id"
                        class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('incorporation_country_id', $organization?->incorporation_country_id) === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="business_ntn" value="NTN Number (if available)" />
                    <x-input id="business_ntn" name="business_ntn" type="text" class="mt-1 block w-full"
                        :value="old('business_ntn', $organization?->ntn)" />
                </div>

                <div>
                    <x-label for="sales_tax_registration_number" value="Sales Tax Registration No." />
                    <x-input id="sales_tax_registration_number" name="sales_tax_registration_number" type="text"
                        class="mt-1 block w-full" :value="old('sales_tax_registration_number', $organization?->sales_tax_registration_number)" />
                </div>

                <div>
                    <x-label for="chamber_membership_number" value="Chamber of Commerce / Trade Body No." />
                    <x-input id="chamber_membership_number" name="chamber_membership_number" type="text"
                        class="mt-1 block w-full" :value="old('chamber_membership_number', $organization?->chamber_membership_number)" />
                </div>

                <div>
                    <x-label for="number_of_employees" value="Number of Employees" />
                    <x-input id="number_of_employees" name="number_of_employees" type="number" min="0"
                        class="mt-1 block w-full" :value="old('number_of_employees', $organization?->number_of_employees)" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="main_geographic_area" value="Main Geographic Area of Activity" />
                    <x-input id="main_geographic_area" name="main_geographic_area" type="text"
                        class="mt-1 block w-full" :value="old('main_geographic_area', $organization?->main_geographic_area)" />
                </div>

                <div>
                    <x-label for="parent_company_name" value="Parent Company / Group Name" />
                    <x-input id="parent_company_name" name="parent_company_name" type="text" class="mt-1 block w-full"
                        :value="old('parent_company_name', $organization?->parent_company_name)" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="other_business_of_proprietor" value="Other Business of the Proprietor" />
                    <x-input id="other_business_of_proprietor" name="other_business_of_proprietor" type="text"
                        class="mt-1 block w-full" :value="old('other_business_of_proprietor', $organization?->other_business_of_proprietor)" />
                </div>

                <div>
                    <x-label for="outside_pakistan_country" value="If Outside Pakistan — Country" />
                    <x-input id="outside_pakistan_country" name="outside_pakistan_country" type="text"
                        class="mt-1 block w-full" :value="old('outside_pakistan_country', $organization?->outside_pakistan_country)" />
                </div>

                <div>
                    <x-label for="outside_pakistan_province" value="If Outside Pakistan — Province / State" />
                    <x-input id="outside_pakistan_province" name="outside_pakistan_province" type="text"
                        class="mt-1 block w-full" :value="old('outside_pakistan_province', $organization?->outside_pakistan_province)" />
                </div>

                <div class="md:col-span-3">
                    <x-label for="group_companies" value="Other Companies of the Group" />
                    <textarea id="group_companies" name="group_companies" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('group_companies', $organization?->group_companies) }}</textarea>
                </div>

                <div class="md:col-span-3">
                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="tax_exempt_on_cash_withdrawal" value="0">
                            <input type="checkbox" name="tax_exempt_on_cash_withdrawal" value="1"
                                @checked(old('tax_exempt_on_cash_withdrawal', $organization?->tax_exempt_on_cash_withdrawal))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm">Tax Exemption: On Cash Withdrawal</span>
                        </label>

                        <label class="inline-flex items-center">
                            <input type="hidden" name="tax_exempt_on_profit" value="0">
                            <input type="checkbox" name="tax_exempt_on_profit" value="1"
                                @checked(old('tax_exempt_on_profit', $organization?->tax_exempt_on_profit))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm">Tax Exemption: On Profit</span>
                        </label>

                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_dnfbp" value="0">
                            <input type="checkbox" name="is_dnfbp" value="1"
                                @checked(old('is_dnfbp', $organization?->is_dnfbp))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm">Is a DNFBP?</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-aof-section>
    @endif
</x-aof-step>
