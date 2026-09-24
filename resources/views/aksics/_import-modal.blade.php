        <div x-data="{ show: false, isSubmitting: false }" x-on:open-import-aksic-modal.window="show = true; isSubmitting = false"
            x-on:keydown.escape.window="if (show) { show = false }" x-show="show" x-cloak class="fixed inset-0 z-50"
            style="display: none;">
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
                    <form method="POST" action="{{ route('aksic.import') }}" enctype="multipart/form-data"
                        @submit="if (isSubmitting) { $event.preventDefault(); return; } isSubmitting = true;">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div>
                                <div class="w-full text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Import AKSIC Excel</h3>
                                    <div class="mt-4 max-w-sm">
                                        <x-label for="aksic_import_file" value="Excel File" :required="true" />
                                        <input id="aksic_import_file" type="file" name="file" accept=".xlsx" required
                                            class="mt-1 block w-full rounded-md border border-gray-300 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                            <button type="button" @click="show = false"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-green-950 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-show="!isSubmitting">Import</span>
                                <span x-show="isSubmitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
