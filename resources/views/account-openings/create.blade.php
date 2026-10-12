@php
    /**
     * New Account Opening -- same screen pattern as Settings > Users > Add user:
     * numbered cards, a live summary beside them and a sticky action bar. The
     * fields and names are the ones StoreAccountOpeningRequest validates.
     */
    $ui = \App\Support\Ui::class;
    $formTypes = [
        'individual_joint_sole' => 'Individual / Joint & Sole Proprietor (15 pages)',
        'entity' => 'Government, Partnership, Public/Private Ltd, NGO/NPO, Club, Society (17 pages)',
    ];
    $branchLabels = $branches->mapWithKeys(fn ($b) => [(string) $b->id => trim(($b->code ? $b->code.' - ' : '').$b->name)]);
    $categoryNames = $categories->mapWithKeys(fn ($c) => [(string) $c->id => $c->name]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('account-openings.index') }}">Account opening</a><span aria-hidden="true">›</span><span>New</span>
                </nav>
                <h1 class="ak-title">New Account Opening</h1>
                <p class="ak-sub">Pick the printed form and the customer category; the wizard then shows only the sections that form contains.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ route('account-openings.index') }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back to list</a>
            </div>
        </div>
    </x-slot>

    @include('settings.partials.ui-style')

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
            <x-status-message />

            @if ($errors->any())
                <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3" role="alert">
                    <p class="text-sm font-bold text-red-800">Please fix the following before continuing:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('account-openings.store') }}" id="aof-create-form"
                x-data="{
                    formType: @js(old('aof_form_type', 'individual_joint_sole')),
                    categoryId: @js((string) old('customer_category_id', '')),
                    branchId: @js((string) old('branch_id', $defaultBranchId ?? '')),
                    sectorCode: @js((string) old('economic_sector_code', '')),
                    requestDate: @js(old('request_date', now()->toDateString())),
                    formNames: @js($formTypes),
                    branchNames: @js($branchLabels),
                    categoryNames: @js($categoryNames),
                    get isEntity() { return this.formType === 'entity'; },
                }"
                {{-- select2 fires a jQuery change, which Alpine's x-model does not see --}}
                x-init="$nextTick(() => { if (window.jQuery) { window.jQuery('#branch_id').on('change', (e) => { branchId = e.target.value; }); } })">
                @csrf

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                    {{-- 1. Form ----------------------------------------------------- --}}
                    <section class="{{ $ui::CARD }} lg:col-span-2">
                        <header class="{{ $ui::CARD_HEAD }}">
                            <h3 class="{{ $ui::CARD_TITLE }}">1. Account opening form</h3>
                            <p class="{{ $ui::CARD_SUBTITLE }}">Which printed form is being filled, the date and the branch. Form reference: AOF page 1 — Particulars of Account.</p>
                        </header>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="aof_form_type" class="{{ $ui::LABEL }}">Which printed form? <span class="text-red-600">*</span></label>
                                <select id="aof_form_type" name="aof_form_type" x-model="formType" required class="{{ $ui::CONTROL }}">
                                    @foreach ($formTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error for="aof_form_type" class="mt-1" />
                            </div>

                            <div>
                                <label for="request_date" class="{{ $ui::LABEL }}">Date <span class="text-red-600">*</span></label>
                                <input id="request_date" name="request_date" type="date" required x-model="requestDate" class="{{ $ui::CONTROL }}">
                                <x-input-error for="request_date" class="mt-1" />
                            </div>

                            <div>
                                <label for="branch_id" class="{{ $ui::LABEL }}">Branch code <span class="text-red-600">*</span></label>
                                <select id="branch_id" name="branch_id" required x-model="branchId" class="select2 {{ $ui::CONTROL }}">
                                    <option value="">Select branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $defaultBranchId) === (string) $branch->id)>
                                            {{ $branch->code }} - {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="branch_id" class="mt-1" />
                            </div>

                            {{-- Typed by hand on the Government / organisation form; Individual accounts do not need it. --}}
                            <div class="md:col-span-2" x-show="isEntity" x-cloak>
                                <label for="economic_sector_code" class="{{ $ui::LABEL }}">Economic Sector Code <span class="text-red-600">*</span></label>
                                <input id="economic_sector_code" name="economic_sector_code" type="text" maxlength="10" autocomplete="off"
                                    placeholder="Enter the code" x-model="sectorCode" x-bind:disabled="!isEntity" x-bind:required="isEntity"
                                    class="{{ $ui::CONTROL }}">
                                <x-input-error for="economic_sector_code" class="mt-1" />
                                <p class="{{ $ui::HINT }}">Required for Government / organisation accounts. Type the code directly.</p>
                            </div>
                        </div>
                    </section>

                    {{-- Live summary ------------------------------------------------- --}}
                    <section class="{{ $ui::CARD }}" aria-label="Summary">
                        <header class="{{ $ui::CARD_HEAD }}">
                            <h3 class="{{ $ui::CARD_TITLE }}">Summary</h3>
                            <p class="{{ $ui::CARD_SUBTITLE }}">Updates as you fill the form.</p>
                        </header>
                        <dl class="space-y-2 p-5 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-gray-700">Form</dt><dd class="text-right font-semibold text-black" x-text="isEntity ? 'Entity (17 pages)' : 'Individual (15 pages)'"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-700">Date</dt><dd class="font-semibold text-black" x-text="requestDate || '—'"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-700">Branch</dt><dd class="text-right font-semibold text-black" x-text="branchNames[branchId] || 'Not selected'"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-700">Category</dt><dd class="text-right font-semibold text-black" x-text="categoryNames[categoryId] || 'Not selected'"></dd></div>
                            <div class="flex justify-between gap-3" x-show="isEntity" x-cloak><dt class="text-gray-700">Economic sector</dt><dd class="font-semibold text-black" x-text="sectorCode || 'Not entered'"></dd></div>
                        </dl>
                    </section>
                </div>

                {{-- 2. Category -------------------------------------------------- --}}
                <section class="{{ $ui::CARD }} mt-5">
                    <header class="{{ $ui::CARD_HEAD }}">
                        <h3 class="{{ $ui::CARD_TITLE }}">2. Customer category</h3>
                        <p class="{{ $ui::CARD_SUBTITLE }}">Individual details appear for the 15-page form, business details for the 17-page form, and both for a Sole Proprietorship. Form reference: CIF page — Customer Category.</p>
                    </header>
                    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                        <div>
                            <label for="customer_category_id" class="{{ $ui::LABEL }}">Category <span class="text-red-600">*</span></label>
                            <select id="customer_category_id" name="customer_category_id" x-model="categoryId" required class="{{ $ui::CONTROL }}">
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
                            <label for="customer_category_other" class="{{ $ui::LABEL }}">If Others, please specify</label>
                            <input id="customer_category_other" name="customer_category_other" type="text" maxlength="150"
                                value="{{ old('customer_category_other') }}" class="{{ $ui::CONTROL }}">
                            <x-input-error for="customer_category_other" class="mt-1" />
                        </div>

                        <div class="md:col-span-2">
                            <label for="category_code_description" class="{{ $ui::LABEL }}">Category code &amp; description</label>
                            <input id="category_code_description" name="category_code_description" type="text" maxlength="200"
                                value="{{ old('category_code_description') }}" class="{{ $ui::CONTROL }}">
                            <x-input-error for="category_code_description" class="mt-1" />
                        </div>
                    </div>
                </section>

                {{-- Sticky action bar ------------------------------------------------ --}}
                <div class="{{ $ui::ACTION_BAR }}">
                    <p class="text-xs text-gray-700">Fields marked <span class="font-bold text-red-600">*</span> are required. After starting, the wizard takes you through each section.</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('account-openings.index') }}" class="{{ $ui::BTN_SECONDARY }}">Cancel</a>
                        <button type="submit" class="{{ $ui::BTN_PRIMARY }}">Start form</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
