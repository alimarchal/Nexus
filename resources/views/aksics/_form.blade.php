<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <section class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Applicant & Business</h3>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <x-label for="application_no" value="Application No" :required="true" />
                <x-input id="application_no" type="text" name="application_no" class="mt-1 block w-full"
                    :value="old('application_no', $aksic->application_no ?? '')" required />
            </div>

            <div>
                <x-label for="status_display" value="Status" />
                <x-input id="status_display" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900"
                    :value="$aksic->status ?? 'Pending'" readonly />
            </div>

            <div>
                <x-label for="name" value="Applicant Name" :required="true" />
                <x-input id="name" type="text" name="name" class="mt-1 block w-full"
                    :value="old('name', $aksic->name ?? '')" required />
            </div>

            <div>
                <x-label for="father_name" value="Father Name" :required="true" />
                <x-input id="father_name" type="text" name="father_name" class="mt-1 block w-full"
                    :value="old('father_name', $aksic->father_name ?? '')" required />
            </div>

            <div>
                <x-label for="cnic" value="CNIC" :required="true" />
                <x-input id="cnic" type="text" name="cnic" class="mt-1 block w-full"
                    :value="old('cnic', $aksic->cnic ?? '')" required />
            </div>

            <div>
                <x-label for="phone" value="Phone" />
                <x-input id="phone" type="text" name="phone" class="mt-1 block w-full"
                    :value="old('phone', $aksic->phone ?? '')" />
            </div>

            <div>
                <x-label for="business_name" value="Business Name" />
                <x-input id="business_name" type="text" name="business_name" class="mt-1 block w-full"
                    :value="old('business_name', $aksic->business_name ?? '')" />
            </div>

            <div>
                <x-label for="business_type" value="Business Type" :required="true" />
                <select id="business_type" name="business_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                    <option value="">Select Business Type</option>
                    @foreach (['Existing', 'New'] as $businessType)
                        <option value="{{ $businessType }}" @selected(old('business_type', $aksic->business_type ?? '') === $businessType)>{{ $businessType }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="business_category_id" value="Business Category" />
                <select id="business_category_id" name="business_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('business_category_id', $aksic->business_category_id ?? '') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="business_sub_category_id" value="Business Sub Category" />
                <select id="business_sub_category_id" name="business_sub_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">Select Business Category first</option>
                </select>
            </div>

            <div>
                <x-label for="district_id" value="District" :required="true" />
                <select id="district_id" name="district_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                    <option value="">Select District</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected((string) old('district_id', $aksic->district_id ?? '') === (string) $district->id)>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="branch_id" value="Branch" />
                <select id="branch_id" name="branch_id" data-placeholder="Select Branch"
                    class="select2 mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">Select Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $aksic->branch_id ?? '') === (string) $branch->id)>
                            {{ $branch->code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="quota" value="Gender Quota" :required="true" />
                <select id="quota" name="quota" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                    <option value="">Select Quota</option>
                    @foreach (['Male', 'Female', 'Disabled', 'Transgender'] as $quota)
                        <option value="{{ $quota }}" @selected(old('quota', $aksic->quota ?? '') === $quota)>{{ $quota }}</option>
                    @endforeach
                </select>
            </div>

            <div id="disabled_gender_wrapper">
                <x-label for="gender" value="Disabled Gender" :required="true" />
                <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">Select Gender</option>
                    @foreach (['Male', 'Female'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $aksic->gender ?? '') === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <x-label for="business_address" value="Business Address" />
                <textarea id="business_address" name="business_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('business_address', $aksic->business_address ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <x-label for="permanent_address" value="Permanent Address" />
                <textarea id="permanent_address" name="permanent_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('permanent_address', $aksic->permanent_address ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Financial & Security</h3>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <x-label for="principal_amount" value="Principal Amount" :required="true" />
                <x-input id="principal_amount" type="number" step="0.01" name="principal_amount" class="mt-1 block w-full"
                    :value="old('principal_amount', $aksic->principal_amount ?? '')" required />
            </div>

            <div>
                <x-label for="tenure" value="Tenure Months" :required="true" />
                <x-input id="tenure" type="number" min="1" name="tenure" class="mt-1 block w-full"
                    :value="old('tenure', $aksic->tenure ?? 60)" required />
            </div>

            <div>
                <x-label for="disbursement_date" value="Disbursement Date" :required="true" />
                <x-input id="disbursement_date" type="date" name="disbursement_date" class="mt-1 block w-full"
                    :value="old('disbursement_date', isset($aksic) && $aksic->disbursement_date ? $aksic->disbursement_date->format('Y-m-d') : '')" required />
            </div>

            <div>
                <x-label for="site_visit_completed" value="Site Visit Completed" :required="true" />
                <select id="site_visit_completed" name="site_visit_completed" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                    <option value="0" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '0')>No</option>
                    <option value="1" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '1')>Yes</option>
                </select>
            </div>

            <div>
                <x-label for="kibor_rate" value="KIBOR Rate" :required="true" />
                <x-input id="kibor_rate" type="number" step="0.01" name="kibor_rate" class="mt-1 block w-full"
                    :value="old('kibor_rate', $aksic->kibor_rate ?? '')" required />
            </div>

            <div>
                <x-label for="spread_rate" value="Spread Rate" :required="true" />
                <x-input id="spread_rate" type="number" step="0.01" name="spread_rate" class="mt-1 block w-full"
                    :value="old('spread_rate', $aksic->spread_rate ?? '')" required />
            </div>

            <div>
                <x-label for="total_rate" value="Total Rate" />
                <x-input id="total_rate" type="number" step="0.01" class="mt-1 block w-full bg-gray-100 dark:bg-gray-900"
                    :value="old('total_rate', isset($aksic) && $aksic->total_rate !== null ? $aksic->total_rate : '')" readonly />
            </div>

            <div>
                <x-label for="site_visit_date" value="Site Visit Date" />
                <x-input id="site_visit_date" type="date" name="site_visit_date" class="mt-1 block w-full"
                    :value="old('site_visit_date', isset($aksic) && $aksic->site_visit_date ? $aksic->site_visit_date->format('Y-m-d') : '')" />
            </div>

            <div>
                <x-label for="consent_entry" value="Consent Entry" />
                <select id="consent_entry" name="consent_entry" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                    <option value="">Select Consent</option>
                    @foreach (['Yes', 'No'] as $consentEntry)
                        <option value="{{ $consentEntry }}" @selected(old('consent_entry', $aksic->consent_entry ?? '') === $consentEntry)>{{ $consentEntry }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="consent_date" value="Consent Date" />
                <x-input id="consent_date" type="date" name="consent_date" class="mt-1 block w-full"
                    :value="old('consent_date', isset($aksic) && $aksic->consent_date ? $aksic->consent_date->format('Y-m-d') : '')" />
            </div>

            <div class="md:col-span-2">
                <x-label for="liquid_security" value="Liquid Security" />
                <textarea id="liquid_security" name="liquid_security" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('liquid_security', $aksic->liquid_security ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <x-label for="personal_guarantees" value="Personal Guarantees" />
                <textarea id="personal_guarantees" name="personal_guarantees" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('personal_guarantees', $aksic->personal_guarantees ?? '') }}</textarea>
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
                district.title = selectedRule
                    ? `${selectedRule.district_name}: ${selectedRule.proposed_beneficiaries} beneficiaries, ${selectedRule.population_percentage}% population`
                    : 'No active AKSIC rule for this district';
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
