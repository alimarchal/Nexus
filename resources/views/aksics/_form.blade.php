@php
    /**
     * AKSIC -- shared create / edit form.
     *
     * Layout only: the fields, names and ids below are exactly the ones the
     * Store/UpdateAksicRequest validate and the aksics table stores. Nothing is
     * added or removed here -- the form is grouped into cards, labelled with
     * required markers and given per-field validation messages.
     */
    $isEdit = isset($aksic) && $aksic->exists;

    $lbl = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $readonly = $control.' bg-gray-100 text-gray-600 dark:bg-gray-950 dark:text-gray-400';
    $card = 'overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800';
    $cardHead = 'flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-900/40';
    $cardTitle = 'text-sm font-bold uppercase tracking-wide text-green-800 dark:text-green-400';
    $hint = 'mt-1 text-xs text-gray-500 dark:text-gray-400';
@endphp

<div class="space-y-5">

    {{-- 1. Applicant ------------------------------------------------------ --}}
    <section class="{{ $card }}">
        <header class="{{ $cardHead }}">
            <div>
                <h3 class="{{ $cardTitle }}">1. Applicant</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Identity of the borrower and the quota applied for.</p>
            </div>
            <span class="rounded-full bg-gray-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                {{ $isEdit ? 'Editing '.$aksic->application_no : 'New case' }}
            </span>
        </header>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div>
                <label for="application_no" class="{{ $lbl }}">Application No <span class="text-red-600">*</span></label>
                <x-input id="application_no" type="text" name="application_no" class="{{ $control }}"
                    :value="old('application_no', $aksic->application_no ?? '')" required />
                <x-input-error for="application_no" class="mt-1" />
            </div>

            <div>
                <label for="status_display" class="{{ $lbl }}">Status</label>
                <x-input id="status_display" type="text" class="{{ $readonly }}"
                    :value="$aksic->status ?? 'Pending'" readonly />
                <p class="{{ $hint }}">Set by the system; approval generates the schedule.</p>
            </div>

            <div>
                <label for="account_no" class="{{ $lbl }}">Account No</label>
                <x-input id="account_no" type="text" name="account_no" maxlength="50" class="{{ $control }}"
                    :value="old('account_no', $aksic->account_no ?? '')" />
                <p class="{{ $hint }}">Bank account the loan is booked against.</p>
                <x-input-error for="account_no" class="mt-1" />
            </div>

            <div>
                <label for="name" class="{{ $lbl }}">Applicant Name <span class="text-red-600">*</span></label>
                <x-input id="name" type="text" name="name" class="{{ $control }}"
                    :value="old('name', $aksic->name ?? '')" required />
                <x-input-error for="name" class="mt-1" />
            </div>

            <div>
                <label for="father_name" class="{{ $lbl }}">Father Name <span class="text-red-600">*</span></label>
                <x-input id="father_name" type="text" name="father_name" class="{{ $control }}"
                    :value="old('father_name', $aksic->father_name ?? '')" required />
                <x-input-error for="father_name" class="mt-1" />
            </div>

            <div>
                <label for="cnic" class="{{ $lbl }}">CNIC <span class="text-red-600">*</span></label>
                <x-input id="cnic" type="text" name="cnic" class="{{ $control }}"
                    :value="old('cnic', $aksic->cnic ?? '')" required />
                <x-input-error for="cnic" class="mt-1" />
            </div>

            <div>
                <label for="phone" class="{{ $lbl }}">Phone</label>
                <x-input id="phone" type="text" name="phone" class="{{ $control }}"
                    :value="old('phone', $aksic->phone ?? '')" />
                <x-input-error for="phone" class="mt-1" />
            </div>

            <div>
                <label for="quota" class="{{ $lbl }}">Gender Quota <span class="text-red-600">*</span></label>
                <select id="quota" name="quota" class="{{ $control }}" required>
                    <option value="">Select Quota</option>
                    @foreach (['Male', 'Female', 'Disabled', 'Transgender'] as $quota)
                        <option value="{{ $quota }}" @selected(old('quota', $aksic->quota ?? '') === $quota)>{{ $quota }}</option>
                    @endforeach
                </select>
                <x-input-error for="quota" class="mt-1" />
            </div>

            <div id="disabled_gender_wrapper">
                <label for="gender" class="{{ $lbl }}">Disabled Gender <span class="text-red-600">*</span></label>
                <select id="gender" name="gender" class="{{ $control }}">
                    <option value="">Select Gender</option>
                    @foreach (['Male', 'Female'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $aksic->gender ?? '') === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
                <p class="{{ $hint }}">Required only when the quota is Disabled / Special Person.</p>
                <x-input-error for="gender" class="mt-1" />
            </div>

            <div class="md:col-span-3">
                <label for="permanent_address" class="{{ $lbl }}">Permanent Address</label>
                <textarea id="permanent_address" name="permanent_address" rows="2" class="{{ $control }}">{{ old('permanent_address', $aksic->permanent_address ?? '') }}</textarea>
                <x-input-error for="permanent_address" class="mt-1" />
            </div>
        </div>
    </section>

    {{-- 2. Business & location -------------------------------------------- --}}
    <section class="{{ $card }}">
        <header class="{{ $cardHead }}">
            <div>
                <h3 class="{{ $cardTitle }}">2. Business &amp; Location</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">What is being financed, where it operates and which branch books it.</p>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div>
                <label for="business_name" class="{{ $lbl }}">Business Name</label>
                <x-input id="business_name" type="text" name="business_name" class="{{ $control }}"
                    :value="old('business_name', $aksic->business_name ?? '')" />
                <x-input-error for="business_name" class="mt-1" />
            </div>

            <div>
                <label for="business_type" class="{{ $lbl }}">Business Type <span class="text-red-600">*</span></label>
                <select id="business_type" name="business_type" class="{{ $control }}" required>
                    <option value="">Select Business Type</option>
                    @foreach (['Existing', 'New'] as $businessType)
                        <option value="{{ $businessType }}" @selected(old('business_type', $aksic->business_type ?? '') === $businessType)>{{ $businessType }}</option>
                    @endforeach
                </select>
                <p class="{{ $hint }}">"New" marks the case as a start-up business.</p>
                <x-input-error for="business_type" class="mt-1" />
            </div>

            <div>
                <label for="business_category_id" class="{{ $lbl }}">Business Category</label>
                <select id="business_category_id" name="business_category_id" class="{{ $control }}">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('business_category_id', $aksic->business_category_id ?? '') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="business_category_id" class="mt-1" />
            </div>

            <div>
                <label for="business_sub_category_id" class="{{ $lbl }}">Business Sub Category</label>
                <select id="business_sub_category_id" name="business_sub_category_id" class="{{ $control }}">
                    <option value="">Select Business Category first</option>
                </select>
                <x-input-error for="business_sub_category_id" class="mt-1" />
            </div>

            <div>
                <label for="district_id" class="{{ $lbl }}">District <span class="text-red-600">*</span></label>
                <select id="district_id" name="district_id" class="{{ $control }}" required>
                    <option value="">Select District</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected((string) old('district_id', $aksic->district_id ?? '') === (string) $district->id)>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="district_id" class="mt-1" />
            </div>

            <div>
                <label for="branch_id" class="{{ $lbl }}">Branch</label>
                <select id="branch_id" name="branch_id" data-placeholder="Select Branch" class="select2 {{ $control }}">
                    <option value="">Select Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $aksic->branch_id ?? '') === (string) $branch->id)>
                            {{ $branch->code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error for="branch_id" class="mt-1" />
            </div>

            {{-- Live read-out of the active scheme rule for the chosen district. --}}
            <div id="district_rule_panel" class="hidden md:col-span-3">
                <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-900 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                    <span class="font-bold uppercase tracking-wide">Scheme rule</span>
                    <span id="district_rule_text" class="ml-2"></span>
                </div>
            </div>

            <div class="md:col-span-3">
                <label for="business_address" class="{{ $lbl }}">Business Address</label>
                <textarea id="business_address" name="business_address" rows="2" class="{{ $control }}">{{ old('business_address', $aksic->business_address ?? '') }}</textarea>
                <x-input-error for="business_address" class="mt-1" />
            </div>
        </div>
    </section>

    {{-- 3. Financing & security ------------------------------------------- --}}
    <section class="{{ $card }}">
        <header class="{{ $cardHead }}">
            <div>
                <h3 class="{{ $cardTitle }}">3. Financing &amp; Security</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Amount, pricing and the due diligence that must be in place before approval.</p>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div>
                <label for="principal_amount" class="{{ $lbl }}">Principal Amount <span class="text-red-600">*</span></label>
                <x-input id="principal_amount" type="number" step="0.01" name="principal_amount" class="{{ $control }}"
                    :value="old('principal_amount', $aksic->principal_amount ?? '')" required />
                <x-input-error for="principal_amount" class="mt-1" />
            </div>

            <div>
                <label for="tenure" class="{{ $lbl }}">Tenure (Months) <span class="text-red-600">*</span></label>
                <x-input id="tenure" type="number" min="1" name="tenure" class="{{ $control }}"
                    :value="old('tenure', $aksic->tenure ?? 60)" required />
                <x-input-error for="tenure" class="mt-1" />
            </div>

            <div>
                <label for="disbursement_date" class="{{ $lbl }}">Disbursement Date <span class="text-red-600">*</span></label>
                <x-input id="disbursement_date" type="date" name="disbursement_date" class="{{ $control }}" max="2100-12-31"
                    :value="\App\Support\AksicDate::forInput(old('disbursement_date', $aksic->disbursement_date ?? null))" required />
                <x-input-error for="disbursement_date" class="mt-1" />
            </div>

            <div>
                <label for="kibor_rate" class="{{ $lbl }}">KIBOR Rate (%) <span class="text-red-600">*</span></label>
                <x-input id="kibor_rate" type="number" step="0.01" name="kibor_rate" class="{{ $control }}"
                    :value="old('kibor_rate', $aksic->kibor_rate ?? '')" required />
                <x-input-error for="kibor_rate" class="mt-1" />
            </div>

            <div>
                <label for="spread_rate" class="{{ $lbl }}">Spread Rate (%) <span class="text-red-600">*</span></label>
                <x-input id="spread_rate" type="number" step="0.01" name="spread_rate" class="{{ $control }}"
                    :value="old('spread_rate', $aksic->spread_rate ?? '')" required />
                <x-input-error for="spread_rate" class="mt-1" />
            </div>

            <div>
                <label for="total_rate" class="{{ $lbl }}">Total Rate (%)</label>
                <x-input id="total_rate" type="number" step="0.01" class="{{ $readonly }}"
                    :value="old('total_rate', isset($aksic) && $aksic->total_rate !== null ? $aksic->total_rate : '')" readonly />
                <p class="{{ $hint }}">KIBOR + Spread, recalculated as you type.</p>
            </div>

            <div>
                <label for="site_visit_completed" class="{{ $lbl }}">Site Visit Completed <span class="text-red-600">*</span></label>
                <select id="site_visit_completed" name="site_visit_completed" class="{{ $control }}" required>
                    <option value="0" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '0')>No</option>
                    <option value="1" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '1')>Yes</option>
                </select>
                <x-input-error for="site_visit_completed" class="mt-1" />
            </div>

            <div>
                <label for="site_visit_date" class="{{ $lbl }}">Site Visit Date</label>
                <x-input id="site_visit_date" type="date" name="site_visit_date" class="{{ $control }}" max="2100-12-31"
                    :value="\App\Support\AksicDate::forInput(old('site_visit_date', $aksic->site_visit_date ?? null))" />
                <x-input-error for="site_visit_date" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="consent_entry" class="{{ $lbl }}">Consent Entry</label>
                    <select id="consent_entry" name="consent_entry" class="{{ $control }}">
                        <option value="">Select</option>
                        @foreach (['Yes', 'No'] as $consentEntry)
                            <option value="{{ $consentEntry }}" @selected(old('consent_entry', $aksic->consent_entry ?? '') === $consentEntry)>{{ $consentEntry }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="consent_entry" class="mt-1" />
                </div>

                <div>
                    <label for="consent_date" class="{{ $lbl }}">Consent Date</label>
                    <x-input id="consent_date" type="date" name="consent_date" class="{{ $control }}" max="2100-12-31"
                        :value="\App\Support\AksicDate::forInput(old('consent_date', $aksic->consent_date ?? null))" />
                    <x-input-error for="consent_date" class="mt-1" />
                </div>
            </div>

            <div class="md:col-span-3 md:grid md:grid-cols-3 md:gap-4">
                <div>
                    <label for="liquid_security" class="{{ $lbl }}">Liquid Security</label>
                    <textarea id="liquid_security" name="liquid_security" rows="3" class="{{ $control }}">{{ old('liquid_security', $aksic->liquid_security ?? '') }}</textarea>
                    <x-input-error for="liquid_security" class="mt-1" />
                </div>

                <div class="mt-4 md:mt-0">
                    <label for="personal_guarantees" class="{{ $lbl }}">Personal Guarantees</label>
                    <textarea id="personal_guarantees" name="personal_guarantees" rows="3" class="{{ $control }}">{{ old('personal_guarantees', $aksic->personal_guarantees ?? '') }}</textarea>
                    <x-input-error for="personal_guarantees" class="mt-1" />
                </div>

                <div class="mt-4 md:mt-0">
                    <label for="mortgage" class="{{ $lbl }}">Mortgage</label>
                    <textarea id="mortgage" name="mortgage" rows="3" class="{{ $control }}"
                        placeholder="Mortgaged property / collateral details">{{ old('mortgage', $aksic->mortgage ?? '') }}</textarea>
                    <x-input-error for="mortgage" class="mt-1" />
                </div>
            </div>
        </div>
    </section>
</div>

@push('modals')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kiborRate = document.getElementById('kibor_rate');
            const spreadRate = document.getElementById('spread_rate');
            const totalRate = document.getElementById('total_rate');
            const businessCategory = document.getElementById('business_category_id');
            const businessSubCategory = document.getElementById('business_sub_category_id');
            const subCategoriesByParent = {{ Illuminate\Support\Js::from($subCategoriesByParent) }};
            const selectedSubCategoryId = String(@json((string) old('business_sub_category_id', $aksic->business_sub_category_id ?? '')));
            const district = document.getElementById('district_id');
            const districtRulePanel = document.getElementById('district_rule_panel');
            const districtRuleText = document.getElementById('district_rule_text');
            const quota = document.getElementById('quota');
            const disabledGenderWrapper = document.getElementById('disabled_gender_wrapper');
            const gender = document.getElementById('gender');
            const rulesByDistrict = {{ Illuminate\Support\Js::from($rulesByDistrict) }};

            function updateTotalRate() {
                const kibor = parseFloat(kiborRate.value);
                const spread = parseFloat(spreadRate.value);

                if (Number.isNaN(kibor) || Number.isNaN(spread)) {
                    totalRate.value = '';
                    return;
                }

                totalRate.value = (kibor + spread).toFixed(2);
            }

            function loadBusinessSubCategories() {
                const parentId = businessCategory.value;
                const subCategories = subCategoriesByParent[parentId] || [];

                businessSubCategory.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = subCategories.length > 0 ? 'Select Sub Category' : 'No Sub Category Available';
                businessSubCategory.appendChild(placeholder);

                subCategories.forEach(function(category) {
                    const option = document.createElement('option');
                    option.value = String(category.id);
                    option.textContent = category.name;
                    option.selected = String(category.id) === selectedSubCategoryId;
                    businessSubCategory.appendChild(option);
                });
            }

            function showDistrictRule() {
                const selectedRule = rulesByDistrict[district.value];

                if (!district.value) {
                    districtRulePanel.classList.add('hidden');
                    district.title = '';
                    return;
                }

                districtRulePanel.classList.remove('hidden');

                if (selectedRule) {
                    const summary = `${selectedRule.district_name}: ${selectedRule.proposed_beneficiaries} proposed beneficiaries, ${selectedRule.population_percentage}% of population.`;
                    districtRuleText.textContent = summary;
                    district.title = summary;
                } else {
                    districtRuleText.textContent = 'No active AKSIC rule exists for this district — the case cannot be saved until one is configured.';
                    district.title = 'No active AKSIC rule for this district';
                }
            }

            function syncDisabledGender() {
                const isDisabledQuota = quota.value === 'Disabled' || quota.value === 'Special Person';
                disabledGenderWrapper.style.display = isDisabledQuota ? 'block' : 'none';
                gender.required = isDisabledQuota;

                if (!isDisabledQuota) {
                    gender.value = '';
                }
            }

            kiborRate.addEventListener('input', updateTotalRate);
            spreadRate.addEventListener('input', updateTotalRate);
            businessCategory.addEventListener('change', loadBusinessSubCategories);
            district.addEventListener('change', showDistrictRule);
            quota.addEventListener('change', syncDisabledGender);
            updateTotalRate();
            loadBusinessSubCategories();
            showDistrictRule();
            syncDisabledGender();
        });
    </script>
@endpush
