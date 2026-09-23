{{--
    AKSIC -- "Approve AKSIC" modal (shared by the index tick button and the case page).

    Open it with:  $dispatch('open-approve-aksic-modal', { url, categoryId, category,
                   principal, kiborRate, spreadRate, totalRate, subCategoryId?, returnTo?, nav? })
    Needs $subCategoriesByParent in the view.
--}}
    <div x-data="{
        show: false,
        actionUrl: '',
        isSubmitting: false,
        selectedSubCategoryId: '',
        returnTo: '',
        nav: '',
        categoryId: '',
        loanInfo: {
            category: '-',
            principal: '-',
            kiborRate: '-',
            spreadRate: '-',
            totalRate: '-',
        },
        subCategoriesByParent: {{ Illuminate\Support\Js::from($subCategoriesByParent) }},
        get subCategories() {
            return this.subCategoriesByParent[this.categoryId] || [];
        },
        open(event) {
            this.show = true;
            this.isSubmitting = false;
            this.selectedSubCategoryId = '';
            const preselected = String(event.detail.subCategoryId || '');
            // options are rendered by x-for, so select the saved sub category once they exist
            setTimeout(() => { this.selectedSubCategoryId = preselected; }, 0);
            this.returnTo = event.detail.returnTo || '';
            this.nav = event.detail.nav || '';
            this.actionUrl = event.detail.url;
            this.categoryId = String(event.detail.categoryId || '');
            this.loanInfo = {
                category: event.detail.category || '-',
                principal: event.detail.principal || '-',
                kiborRate: event.detail.kiborRate || '-',
                spreadRate: event.detail.spreadRate || '-',
                totalRate: event.detail.totalRate || '-',
            };
        },
    }" x-on:open-approve-aksic-modal.window="open($event)" x-on:keydown.escape.window="if (show) { show = false }"
        x-show="show" x-cloak class="fixed inset-0 z-50" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 backdrop-blur-none"
            x-transition:enter-end="opacity-100 backdrop-blur-sm"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 backdrop-blur-sm"
            x-transition:leave-end="opacity-0 backdrop-blur-none"
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-all" @click="show = false">
        </div>

        <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                @click.outside="show = false">
                <form :action="actionUrl" method="POST"
                    @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true;">
                    @csrf
                    <input type="hidden" name="return_to" :value="returnTo">
                    <input type="hidden" name="nav" :value="nav">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                                <svg class="size-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Approve AKSIC</h3>
                                <div class="mt-2 space-y-4">
                                    <p class="text-sm text-gray-600">
                                        Select Business Sub Category before approving. This will generate the amortization schedule and save the total interest.
                                    </p>
                                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Loan Information</div>
                                        <dl class="mt-2 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                            <div>
                                                <dt class="text-gray-500">Business Category</dt>
                                                <dd class="font-semibold text-gray-900" x-text="loanInfo.category"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Principal</dt>
                                                <dd class="font-semibold text-gray-900" x-text="loanInfo.principal"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">KIBOR</dt>
                                                <dd class="font-semibold text-gray-900" x-text="loanInfo.kiborRate"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-gray-500">Spread</dt>
                                                <dd class="font-semibold text-gray-900" x-text="loanInfo.spreadRate"></dd>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <dt class="text-gray-500">Total Rate</dt>
                                                <dd class="font-semibold text-gray-900" x-text="loanInfo.totalRate"></dd>
                                            </div>
                                        </dl>
                                    </div>
                                    <div>
                                        <x-label for="approve_business_sub_category_id" value="Business Sub Category" :required="true" />
                                        <select id="approve_business_sub_category_id" name="business_sub_category_id"
                                            x-model="selectedSubCategoryId" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Business Sub Category</option>
                                            <template x-for="category in subCategories" :key="category.id">
                                                <option :value="category.id" x-text="category.name" :selected="String(category.id) === selectedSubCategoryId"></option>
                                            </template>
                                        </select>
                                        <p x-show="subCategories.length === 0" class="mt-2 text-xs text-red-600">
                                            No sub category is available for this AKSIC business category.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                        <button type="button" @click="show = false"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting || !selectedSubCategoryId"
                            class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-show="!isSubmitting">Approve</span>
                            <span x-show="isSubmitting">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
