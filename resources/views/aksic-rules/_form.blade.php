<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
        <x-label for="district_id" value="District" :required="true" />
        <select id="district_id" name="district_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="">Select District</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" @selected((string) old('district_id', $aksicRule->district_id ?? '') === (string) $district->id)>
                    {{ $district->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="population_percentage" value="Population %" :required="true" />
        <x-input id="population_percentage" type="number" step="0.01" min="0" max="100" name="population_percentage"
            class="mt-1 block w-full" :value="old('population_percentage', $aksicRule->population_percentage ?? '')" required />
    </div>

    <div>
        <x-label for="proposed_beneficiaries" value="Proposed Beneficiaries" :required="true" />
        <x-input id="proposed_beneficiaries" type="number" min="1" name="proposed_beneficiaries"
            class="mt-1 block w-full" :value="old('proposed_beneficiaries', $aksicRule->proposed_beneficiaries ?? '')" required />
    </div>

    <div>
        <x-label for="is_active" value="Status" :required="true" />
        <select id="is_active" name="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="1" @selected((string) old('is_active', isset($aksicRule) ? (int) $aksicRule->is_active : 1) === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', isset($aksicRule) ? (int) $aksicRule->is_active : 1) === '0')>Inactive</option>
        </select>
    </div>

    <div>
        <x-label for="male_percentage" value="Male %" :required="true" />
        <x-input id="male_percentage" type="number" step="0.01" min="0" max="100" name="male_percentage"
            class="mt-1 block w-full" :value="old('male_percentage', $aksicRule->male_percentage ?? 48)" required />
    </div>

    <div>
        <x-label for="female_percentage" value="Female %" :required="true" />
        <x-input id="female_percentage" type="number" step="0.01" min="0" max="100" name="female_percentage"
            class="mt-1 block w-full" :value="old('female_percentage', $aksicRule->female_percentage ?? 48)" required />
    </div>

    <div>
        <x-label for="special_person_percentage" value="Disabled %" :required="true" />
        <x-input id="special_person_percentage" type="number" step="0.01" min="0" max="100" name="special_person_percentage"
            class="mt-1 block w-full" :value="old('special_person_percentage', $aksicRule->special_person_percentage ?? 2)" required />
    </div>

    <div>
        <x-label for="transgender_percentage" value="Transgender %" :required="true" />
        <x-input id="transgender_percentage" type="number" step="0.01" min="0" max="100" name="transgender_percentage"
            class="mt-1 block w-full" :value="old('transgender_percentage', $aksicRule->transgender_percentage ?? 2)" required />
    </div>

    <div>
        <x-label for="requires_site_visit" value="Site Visit Required" :required="true" />
        <select id="requires_site_visit" name="requires_site_visit" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="1" @selected((string) old('requires_site_visit', isset($aksicRule) ? (int) $aksicRule->requires_site_visit : 1) === '1')>Yes</option>
            <option value="0" @selected((string) old('requires_site_visit', isset($aksicRule) ? (int) $aksicRule->requires_site_visit : 1) === '0')>No</option>
        </select>
    </div>

    <div>
        <x-label for="requires_business_nature" value="Business Nature Required" :required="true" />
        <select id="requires_business_nature" name="requires_business_nature" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
            <option value="1" @selected((string) old('requires_business_nature', isset($aksicRule) ? (int) $aksicRule->requires_business_nature : 1) === '1')>Yes</option>
            <option value="0" @selected((string) old('requires_business_nature', isset($aksicRule) ? (int) $aksicRule->requires_business_nature : 1) === '0')>No</option>
        </select>
    </div>

    <div class="md:col-span-2 lg:col-span-4">
        <x-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('notes', $aksicRule->notes ?? '') }}</textarea>
    </div>
</div>
