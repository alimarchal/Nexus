<x-app-layout>
    <x-slot name="header">
        <x-page-header title="New Account Opening" :showSearch="false" :showRefresh="false"
            backRoute="account-openings.index" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4" />

            <div class="overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Pick the printed form and the customer category. The wizard then shows only the sections that
                    form actually contains &mdash; Individual details for the 15-page form, Business details for the
                    17-page form, and both for a Sole Proprietorship.
                </p>

                <form method="POST" action="{{ route('account-openings.store') }}"
                    x-data="{ formType: '{{ old('aof_form_type', 'individual_joint_sole') }}', categoryId: '{{ old('customer_category_id') }}' }">
                    @csrf

                    <x-aof-section title="Account Opening Form" reference="AOF page 1 — Particulars of Account">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-label for="aof_form_type" value="Which printed form?" />
                                <select id="aof_form_type" name="aof_form_type" x-model="formType" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="individual_joint_sole">
                                        Individual / Joint &amp; Sole Proprietor (15 pages)
                                    </option>
                                    <option value="entity">
                                        Government, Partnership, Public/Private Ltd, NGO/NPO, Club, Society (17 pages)
                                    </option>
                                </select>
                                <x-input-error for="aof_form_type" class="mt-1" />
                            </div>

                            <div>
                                <x-label for="request_date" value="Date" />
                                <x-input id="request_date" name="request_date" type="date" class="mt-1 block w-full"
                                    :value="old('request_date', now()->toDateString())" required />
                                <x-input-error for="request_date" class="mt-1" />
                            </div>

                            <div>
                                <x-label for="branch_id" value="Branch Code" />
                                <select id="branch_id" name="branch_id" required
                                    class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @selected((string) old('branch_id', $defaultBranchId) === (string) $branch->id)>
                                            {{ $branch->code }} - {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="branch_id" class="mt-1" />
                            </div>

                            <div>
                                <x-label for="economic_sector_id" value="Economic Sector Code" />
                                <select id="economic_sector_id" name="economic_sector_id"
                                    class="select2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select sector</option>
                                    @foreach ($economicSectors as $sector)
                                        <option value="{{ $sector->id }}" @selected(old('economic_sector_id') === $sector->id)>
                                            {{ $sector->code }} - {{ $sector->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Mandatory when the customer category is a business.</p>
                            </div>
                        </div>
                    </x-aof-section>

                    <x-aof-section title="Customer Category" reference="CIF page — Customer Category">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-label for="customer_category_id" value="Category" />
                                <select id="customer_category_id" name="customer_category_id" x-model="categoryId" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            data-applies-to="{{ $category->applies_to }}"
                                            x-show="formType === 'entity' ? '{{ $category->applies_to }}' !== 'individual' : '{{ $category->applies_to }}' !== 'entity'"
                                            @selected(old('customer_category_id') === $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="customer_category_id" class="mt-1" />
                            </div>

                            <div>
                                <x-label for="customer_category_other" value="If Others, please specify" />
                                <x-input id="customer_category_other" name="customer_category_other" type="text"
                                    class="mt-1 block w-full" :value="old('customer_category_other')" />
                            </div>

                            <div class="md:col-span-2">
                                <x-label for="category_code_description" value="Category Code & Description" />
                                <x-input id="category_code_description" name="category_code_description" type="text"
                                    class="mt-1 block w-full" :value="old('category_code_description')" />
                            </div>
                        </div>
                    </x-aof-section>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('account-openings.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-6 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800">
                            Start Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
