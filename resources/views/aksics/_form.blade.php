<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
        <x-label for="application_no" value="Application No" :required="true" />
        <x-input id="application_no" type="text" name="application_no" class="mt-1 block w-full"
            :value="old('application_no', $aksic->application_no ?? '')" required />
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
        <x-label for="status" value="Status" :required="true" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            @foreach (['Pending', 'Approved'] as $status)
                <option value="{{ $status }}" @selected(old('status', $aksic->status ?? 'Pending') === $status)>{{ $status }}</option>
            @endforeach
        </select>
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
            @foreach (['Existing', 'Startup', 'New'] as $businessType)
                <option value="{{ $businessType }}" @selected(old('business_type', $aksic->business_type ?? '') === $businessType)>{{ $businessType }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="is_startup_business" value="Startup / New Business" :required="true" />
        <select id="is_startup_business" name="is_startup_business" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="0" @selected((string) old('is_startup_business', isset($aksic) ? (int) $aksic->is_startup_business : 0) === '0')>No</option>
            <option value="1" @selected((string) old('is_startup_business', isset($aksic) ? (int) $aksic->is_startup_business : 0) === '1')>Yes</option>
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
        <x-label for="quota" value="Gender Quota" :required="true" />
        <select id="quota" name="quota" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="">Select Quota</option>
            @foreach (['Male', 'Female', 'Special Person', 'Transgender'] as $quota)
                <option value="{{ $quota }}" @selected(old('quota', $aksic->quota ?? '') === $quota)>{{ $quota }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="branch_id" value="Branch" />
        <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
            <option value="">Select Branch</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $aksic->branch_id ?? '') === (string) $branch->id)>
                    {{ $branch->code }} - {{ $branch->name }}
                </option>
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
        <x-label for="sanction_date" value="Sanction Date" />
        <x-input id="sanction_date" type="date" name="sanction_date" class="mt-1 block w-full"
            :value="old('sanction_date', isset($aksic) && $aksic->sanction_date ? $aksic->sanction_date->format('Y-m-d') : '')" />
    </div>

    <div>
        <x-label for="site_visit_completed" value="Site Visit Completed" :required="true" />
        <select id="site_visit_completed" name="site_visit_completed" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="0" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '0')>No</option>
            <option value="1" @selected((string) old('site_visit_completed', isset($aksic) ? (int) $aksic->site_visit_completed : 0) === '1')>Yes</option>
        </select>
    </div>

    <div>
        <x-label for="site_visit_date" value="Site Visit Date" />
        <x-input id="site_visit_date" type="date" name="site_visit_date" class="mt-1 block w-full"
            :value="old('site_visit_date', isset($aksic) && $aksic->site_visit_date ? $aksic->site_visit_date->format('Y-m-d') : '')" />
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

    <div class="md:col-span-2 lg:col-span-4">
        <x-label for="business_address" value="Business Address" />
        <textarea id="business_address" name="business_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('business_address', $aksic->business_address ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2 lg:col-span-4">
        <x-label for="permanent_address" value="Permanent Address" />
        <textarea id="permanent_address" name="permanent_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('permanent_address', $aksic->permanent_address ?? '') }}</textarea>
    </div>
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
            const businessType = document.getElementById('business_type');
            const startup = document.getElementById('is_startup_business');
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

            function syncStartupFlag() {
                startup.value = ['Startup', 'New'].includes(businessType.value) ? '1' : '0';
            }

            function showDistrictRule() {
                const selectedRule = rulesByDistrict[district.value];
                district.title = selectedRule
                    ? `${selectedRule.district_name}: ${selectedRule.proposed_beneficiaries} beneficiaries, ${selectedRule.population_percentage}% population`
                    : 'No active AKSIC rule for this district';
            }

            kiborRate.addEventListener('input', updateTotalRate);
            spreadRate.addEventListener('input', updateTotalRate);
            businessCategory.addEventListener('change', loadBusinessSubCategories);
            businessType.addEventListener('change', syncStartupFlag);
            district.addEventListener('change', showDistrictRule);
            updateTotalRate();
            loadBusinessSubCategories();
            syncStartupFlag();
            showDistrictRule();
        });
    </script>
@endpush
