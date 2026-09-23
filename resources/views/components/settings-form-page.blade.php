{{--
    Standard create/edit page for settings screens (regions, districts, branches,
    permissions, managers ...). Same look as Settings -> Users -> Edit:
    bordered card with grey header strip, black labels, sticky action bar and a
    review modal in the "Approve AKSIC" pattern before anything is saved.

    Fields that should appear in the review modal carry data-review="Label".

    <x-settings-form-page title="Edit Region" :back="route('regions.index')"
        :action="route('regions.update', $region)" method="PUT"
        cardTitle="Region details" confirmTitle="Save region?">
        ...inputs...
    </x-settings-form-page>
--}}
@props([
    'title',
    'subtitle' => null,
    'back',
    'action',
    'method' => 'POST',
    'cardTitle' => 'Details',
    'cardSubtitle' => null,
    'submitLabel' => 'Review & Save',
    'confirmTitle' => 'Save changes?',
    'confirmButton' => 'Save',
    'confirmNote' => null,
    'maxWidth' => 'max-w-7xl',
])

@php($ui = \App\Support\Ui::class)
@php($formId = 'settings-form-'.\Illuminate\Support\Str::random(6))

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
                @endif
            </div>
            <a href="{{ $back }}"
                class="inline-flex items-center gap-2 rounded-md border border-transparent bg-blue-950 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="{{ $maxWidth }} mx-auto space-y-5 sm:px-6 lg:px-8">
            <x-status-message />

            @if ($errors->any())
                <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 shadow-md dark:border-red-800 dark:bg-red-900/30">
                    <p class="text-sm font-bold text-red-800 dark:text-red-200">Please fix the following before saving:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $action }}" id="{{ $formId }}"
                x-data="settingsForm('{{ $formId }}')" @submit.prevent="openReview()">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <section class="{{ $ui::CARD }}">
                    <header class="{{ $ui::CARD_HEAD }}">
                        <h3 class="{{ $ui::CARD_TITLE }}">{{ $cardTitle }}</h3>
                        @if ($cardSubtitle)
                            <p class="{{ $ui::CARD_SUBTITLE }}">{{ $cardSubtitle }}</p>
                        @endif
                    </header>
                    <div class="p-5">
                        {{ $slot }}
                    </div>
                </section>

                {{ $after ?? '' }}

                <div class="{{ $ui::ACTION_BAR }}">
                    <p class="text-xs text-gray-700 dark:text-gray-300">
                        Fields marked <span class="font-bold text-red-600">*</span> are required. You will review before saving.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ $back }}" class="{{ $ui::BTN_SECONDARY }}">Cancel</a>
                        <button type="submit" class="{{ $ui::BTN_PRIMARY }}">{{ $submitLabel }}</button>
                    </div>
                </div>

                {{-- Review modal (same pattern as the "Approve AKSIC" modal) --}}
                <div x-show="review" x-cloak class="fixed inset-0 z-50" style="display: none;" x-on:keydown.escape.window="review = false">
                    <div x-show="review" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="review = false"></div>
                    <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
                        <div x-show="review" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                            class="relative w-full overflow-hidden rounded-lg bg-white text-left shadow-xl sm:max-w-lg" @click.outside="review = false">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                                        <svg class="size-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-medium leading-6 text-black">{{ $confirmTitle }}</h3>
                                        <div class="mt-3 rounded-md border border-gray-400 bg-gray-50 p-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-700">Review</div>
                                            <dl class="mt-2 space-y-1.5 text-sm">
                                                <template x-for="row in rows" :key="row.label">
                                                    <div class="flex justify-between gap-4">
                                                        <dt class="text-gray-700" x-text="row.label"></dt>
                                                        <dd class="text-right font-semibold text-black" x-text="row.value || '—'"></dd>
                                                    </div>
                                                </template>
                                            </dl>
                                        </div>
                                        @if ($confirmNote)
                                            <p class="mt-3 text-sm text-amber-800">&#9888; {{ $confirmNote }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-row justify-end gap-3 bg-gray-100 px-6 py-4">
                                <button type="button" @click="review = false"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">Cancel</button>
                                <button type="button" @click="confirmSave()" :disabled="saving"
                                    class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <span x-show="!saving">{{ $confirmButton }}</span><span x-show="saving">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @once
        @push('modals')
            <script>
                function settingsForm(formId) {
                    return {
                        review: false,
                        saving: false,
                        rows: [],
                        collect() {
                            const form = document.getElementById(formId);
                            const rows = [];
                            form.querySelectorAll('[data-review]').forEach((el) => {
                                let value = '';
                                if (el.tagName === 'SELECT') {
                                    value = el.multiple
                                        ? [...el.selectedOptions].map((o) => o.text.trim()).join(', ')
                                        : (el.value ? el.options[el.selectedIndex].text.trim() : '');
                                } else if (el.type === 'checkbox') {
                                    value = el.checked ? 'Yes' : 'No';
                                } else {
                                    value = (el.value || '').trim();
                                }
                                if (el.dataset.reviewValue) value = el.dataset.reviewValue.replace('{n}', value);
                                rows.push({ label: el.dataset.review, value });
                            });
                            this.rows = rows;
                        },
                        openReview() {
                            const form = document.getElementById(formId);
                            if (!form.reportValidity()) return;
                            this.collect();
                            this.saving = false;
                            this.review = true;
                        },
                        confirmSave() {
                            if (this.saving) return;
                            this.saving = true;
                            document.getElementById(formId).submit();
                        },
                    };
                }
            </script>
        @endpush
    @endonce
</x-app-layout>
