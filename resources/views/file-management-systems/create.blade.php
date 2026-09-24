{{--
    New file: /product/file-management-systems/create
    Same look as the AKSIC / file list pages: breadcrumb header, numbered
    section cards, a live summary on the right, a page uploader that keeps page
    order (add more, reorder, remove), a sticky action bar and a review modal
    (the "Approve AKSIC" pattern) before anything is saved.
--}}
@php
    $ui = \App\Support\Ui::class;
    $officeTypes = ['branch' => 'Branch', 'region' => 'Region', 'division' => 'Division', 'head-office' => 'Head office'];
    $officeOptions = [
        'branch' => $branches->map(fn ($b) => ['id' => $b->id, 'label' => trim(($b->code ? $b->code.' - ' : '').$b->name)])->values(),
        'region' => $regions->map(fn ($r) => ['id' => $r->id, 'label' => $r->name])->values(),
        'division' => $divisions->map(fn ($d) => ['id' => $d->id, 'label' => $d->short_name ? $d->short_name.' - '.$d->name : $d->name])->values(),
        'head-office' => $headOffices->map(fn ($h) => ['id' => $h->id, 'label' => $h->name])->values(),
    ];
    $canCreate = $autoFileable || $isSuperAdmin;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div class="ak-head-text">
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('product.index') }}">Product</a><span aria-hidden="true">›</span>
                    <a href="{{ route('file-management-systems.index') }}">File management</a><span aria-hidden="true">›</span>
                    <span>New file</span>
                </nav>
                <h1 class="ak-title">New file</h1>
                <p class="ak-sub">Record a document and upload its scanned pages. A digital ID is given when you save.</p>
            </div>
            <div class="ak-head-actions">
                <a href="{{ session('fms.list_url', route('file-management-systems.index')) }}" class="ak-btn ak-btn-outline"><span aria-hidden="true">←</span> Back</a>
            </div>
        </div>
    </x-slot>

    @include('aksics._ui-style')
    <style>
        .fc-layout { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr); align-items: start; }
        @media (min-width: 1024px) { .fc-layout { grid-template-columns: minmax(0, 1fr) 320px; } .fc-aside { position: sticky; top: 16px; } }
        .fc-step { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; margin-right: 8px; border-radius: 999px; background: #166534; color: #fff; font-size: 12px; font-weight: 800; }
        .fc-grid { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 768px) { .fc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .fc-grid .wide { grid-column: 1 / -1; } }
        .fc-req { color: #dc2626; font-weight: 700; }
        .fc-drop { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 28px 16px; border: 2px dashed #94a3b8; border-radius: 12px; background: #f8fafc; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
        .fc-drop:hover, .fc-drop:focus-visible, .fc-drop.is-over { border-color: var(--ak-navy); background: #eef2ff; outline: none; }
        .fc-drop svg { width: 36px; height: 36px; color: #64748b; }
        .fc-drop b { color: var(--ak-navy); }
        .fc-files { list-style: none; margin: 14px 0 0; padding: 0; border: 1px solid var(--ak-border); border-radius: 10px; overflow: hidden; }
        .fc-files li { display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: #fff; }
        .fc-files li + li { border-top: 1px solid var(--ak-line); }
        .fc-files li.is-bad { background: #fef2f2; }
        .fc-no { flex: none; width: 60px; font-size: 12px; font-weight: 700; color: var(--ak-muted); }
        .fc-thumb { flex: none; width: 40px; height: 40px; border-radius: 6px; background: var(--ak-soft); border: 1px solid var(--ak-line); display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 10px; font-weight: 800; color: #475569; }
        .fc-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fc-name { flex: 1; min-width: 0; font-size: 13px; }
        .fc-name b { display: block; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--ak-text); }
        .fc-name span { color: var(--ak-muted); font-size: 12px; }
        .fc-name .bad { color: #b91c1c; font-weight: 600; }
        .fc-tools { flex: none; display: flex; gap: 2px; }
        .fc-sum dl { font-size: 13px; }
        .fc-sum dl > div { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--ak-line); }
        .fc-sum dt { color: var(--ak-muted); }
        .fc-sum dd { font-weight: 600; color: var(--ak-text); text-align: right; overflow-wrap: anywhere; }
        .fc-check { list-style: none; margin: 12px 0 0; padding: 0; font-size: 13px; }
        .fc-check li { display: flex; align-items: center; gap: 8px; padding: 3px 0; color: var(--ak-muted); }
        .fc-check li i { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd5e1; flex: none; }
        .fc-check li.ok { color: #166534; }
        .fc-check li.ok i { border-color: #16a34a; background: #16a34a; box-shadow: inset 0 0 0 2px #fff; }
    </style>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-status-message />

            @if ($errors->any())
                <div class="ak-alert ak-alert-error mb-5" role="alert">
                    <b>Please fix the following before saving:</b>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @unless ($canCreate)
                <div class="ak-alert ak-alert-warn" role="alert">
                    Your account is not assigned to an office (branch, region, division or head office), so it cannot record files yet.
                    Ask the administrator to set your office on Settings &rarr; Users.
                </div>
            @else
                <form method="POST" action="{{ route('file-management-systems.store') }}" enctype="multipart/form-data" id="fms-create"
                    x-data="fmsCreate({
                        options: @js($officeOptions),
                        types: @js($officeTypes),
                        auto: @js($autoFileable ? \Illuminate\Support\Str::headline($autoFileable['type']).': '.$autoFileable['label'] : null),
                        type: @js(old('fileable_type', $autoFileable['type'] ?? 'branch')),
                        officeId: @js((string) old('fileable_id', $autoFileable['id'] ?? '')),
                    })"
                    @submit.prevent="openReview()">
                    @csrf

                    <div class="fc-layout">
                        <div class="space-y-5">
                            {{-- 1. Document --}}
                            <section class="{{ $ui::CARD }}">
                                <header class="{{ $ui::CARD_HEAD }}">
                                    <h2 class="{{ $ui::CARD_TITLE }}"><span class="fc-step">1</span>Document details</h2>
                                </header>
                                <div class="fc-grid p-5">
                                    <div>
                                        <label for="file_category_id" class="{{ $ui::LABEL }}">Category <span class="fc-req">*</span></label>
                                        <select id="file_category_id" name="file_category_id" required class="{{ $ui::CONTROL }}" x-model="category">
                                            <option value="">Select a category</option>
                                            @foreach ($fileCategories as $fileCategory)
                                                <option value="{{ $fileCategory->id }}" @selected(old('file_category_id') == $fileCategory->id)>{{ $fileCategory->category_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('file_category_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="document_date" class="{{ $ui::LABEL }}">Document date <span class="fc-req">*</span></label>
                                        <input id="document_date" type="date" name="document_date" required max="{{ now()->toDateString() }}"
                                            value="{{ old('document_date') }}" class="{{ $ui::CONTROL }}" x-model="date">
                                        <p class="{{ $ui::HINT }}">Date written on the document (not today's date).</p>
                                        @error('document_date') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="wide">
                                        <label for="title" class="{{ $ui::LABEL }}">Title</label>
                                        <input id="title" type="text" name="title" maxlength="255" value="{{ old('title') }}" class="{{ $ui::CONTROL }}"
                                            placeholder="e.g. Loan file of Muhammad Ali, A/c 0011-1234567" x-model="title">
                                        <p class="{{ $ui::HINT }}">Shown in the file list and search &mdash; write what someone would search for.</p>
                                        @error('title') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="file_no" class="{{ $ui::LABEL }}">File no <span class="text-xs font-normal text-gray-600">(your own reference)</span></label>
                                        <input id="file_no" type="text" name="file_no" maxlength="60" value="{{ old('file_no') }}" class="{{ $ui::CONTROL }}"
                                            placeholder="e.g. HRMS/12/ABC" x-model="fileNo">
                                        @error('file_no') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </section>

                            {{-- 2. Office --}}
                            <section class="{{ $ui::CARD }}">
                                <header class="{{ $ui::CARD_HEAD }}">
                                    <h2 class="{{ $ui::CARD_TITLE }}"><span class="fc-step">2</span>Owning office</h2>
                                    <p class="{{ $ui::CARD_SUBTITLE }}">The office that keeps this file. Only that office can archive it into its boxes.</p>
                                </header>
                                <div class="fc-grid p-5">
                                    @if ($autoFileable)
                                        <div class="wide">
                                            <p class="{{ $ui::LABEL }}">Office</p>
                                            <p class="mt-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-semibold text-black">
                                                {{ \Illuminate\Support\Str::headline($autoFileable['type']) }}: {{ $autoFileable['label'] }}
                                            </p>
                                            <p class="{{ $ui::HINT }}">Set automatically from your posting.</p>
                                            <input type="hidden" name="fileable_type" value="{{ $autoFileable['type'] }}">
                                            <input type="hidden" name="fileable_id" value="{{ $autoFileable['id'] }}">
                                        </div>
                                    @else
                                        <div>
                                            <label for="fileable_type" class="{{ $ui::LABEL }}">Office type <span class="fc-req">*</span></label>
                                            <select id="fileable_type" name="fileable_type" required class="{{ $ui::CONTROL }}" x-model="type" @change="officeId = ''">
                                                @foreach ($officeTypes as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('fileable_type') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="fileable_id" class="{{ $ui::LABEL }}"><span x-text="types[type]"></span> <span class="fc-req">*</span></label>
                                            <select id="fileable_id" name="fileable_id" required class="{{ $ui::CONTROL }}" x-model="officeId">
                                                <option value="">Select</option>
                                                <template x-for="o in options[type]" :key="type + o.id">
                                                    <option :value="String(o.id)" x-text="o.label" :selected="String(o.id) === officeId"></option>
                                                </template>
                                            </select>
                                            @error('fileable_id') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                        </div>
                                    @endif
                                </div>
                            </section>

                            {{-- 3. Pages --}}
                            <section class="{{ $ui::CARD }}">
                                <header class="{{ $ui::CARD_HEAD }}">
                                    <h2 class="{{ $ui::CARD_TITLE }}"><span class="fc-step">3</span>Scanned pages <span class="fc-req">*</span></h2>
                                    <p class="{{ $ui::CARD_SUBTITLE }}">PDF, Word, JPG or PNG &middot; up to 10 MB each &middot; saved in the order listed.</p>
                                </header>
                                <div class="p-5">
                                    <div class="fc-drop" role="button" tabindex="0" :class="over && 'is-over'"
                                        @click="$refs.picker.click()" @keydown.enter.prevent="$refs.picker.click()" @keydown.space.prevent="$refs.picker.click()"
                                        @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop.prevent="over = false; add($event.dataTransfer.files)"
                                        aria-describedby="fc-drop-help">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                                        <span><b>Choose files</b> or drag them here</span>
                                        <span id="fc-drop-help" class="text-xs text-gray-600">You can add more files later in this list; they are added after the current pages.</span>
                                    </div>
                                    <input type="file" x-ref="picker" multiple class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="add($event.target.files); $event.target.value = ''">
                                    <input type="file" name="pages[]" x-ref="pages" multiple class="hidden">

                                    <template x-if="files.length">
                                        <ol class="fc-files" aria-label="Pages to upload">
                                            <template x-for="(f, i) in files" :key="f.key">
                                                <li :class="f.error && 'is-bad'">
                                                    <span class="fc-no" x-text="'Page ' + (i + 1)"></span>
                                                    <span class="fc-thumb">
                                                        <template x-if="f.preview"><img :src="f.preview" alt=""></template>
                                                        <template x-if="!f.preview"><span x-text="f.ext"></span></template>
                                                    </span>
                                                    <span class="fc-name">
                                                        <b x-text="f.file.name"></b>
                                                        <span x-show="!f.error" x-text="size(f.file.size)"></span>
                                                        <span x-show="f.error" class="bad" x-text="f.error"></span>
                                                    </span>
                                                    <span class="fc-tools">
                                                        <button type="button" class="ak-icon" :disabled="i === 0" @click="move(i, -1)" :aria-label="'Move page ' + (i + 1) + ' up'" title="Move up">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                                                        </button>
                                                        <button type="button" class="ak-icon" :disabled="i === files.length - 1" @click="move(i, 1)" :aria-label="'Move page ' + (i + 1) + ' down'" title="Move down">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                                        </button>
                                                        <button type="button" class="ak-icon" style="color:#b91c1c" @click="remove(i)" :aria-label="'Remove page ' + (i + 1)" title="Remove">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </span>
                                                </li>
                                            </template>
                                        </ol>
                                    </template>
                                    <p class="mt-2 text-sm font-semibold text-red-700" x-show="pagesError" x-text="pagesError" role="alert"></p>
                                    @error('pages') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                    @error('pages.*') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                </div>
                            </section>
                        </div>

                        {{-- Summary --}}
                        <aside class="fc-aside {{ $ui::CARD }} fc-sum" aria-label="Summary">
                            <header class="{{ $ui::CARD_HEAD }}"><h2 class="{{ $ui::CARD_TITLE }}">Summary</h2></header>
                            <div class="p-5">
                                <dl>
                                    <div><dt>Office</dt><dd x-text="officeLabel() || '—'"></dd></div>
                                    <div><dt>Category</dt><dd x-text="categoryLabel() || '—'"></dd></div>
                                    <div><dt>Document date</dt><dd x-text="date ? date.split('-').reverse().join('.') : '—'"></dd></div>
                                    <div><dt>Pages</dt><dd x-text="files.length ? files.length + ' (' + size(totalSize()) + ')' : '—'"></dd></div>
                                    <div><dt>Custodian</dt><dd>{{ auth()->user()->name }}</dd></div>
                                    <div><dt>Digital ID</dt><dd class="text-gray-600">given on save</dd></div>
                                </dl>
                                <ul class="fc-check" aria-label="Ready to save">
                                    <li :class="category && 'ok'"><i aria-hidden="true"></i>Category chosen</li>
                                    <li :class="date && 'ok'"><i aria-hidden="true"></i>Document date entered</li>
                                    <li :class="officeLabel() && 'ok'"><i aria-hidden="true"></i>Office set</li>
                                    <li :class="validFiles().length && !files.some(f => f.error) && 'ok'"><i aria-hidden="true"></i>At least one page added</li>
                                </ul>
                            </div>
                        </aside>
                    </div>

                    <div class="{{ $ui::ACTION_BAR }}">
                        <p class="text-xs text-gray-700">Fields marked <span class="font-bold text-red-600">*</span> are required. You will review before saving.</p>
                        <div class="flex items-center gap-3">
                            <a href="{{ session('fms.list_url', route('file-management-systems.index')) }}" class="{{ $ui::BTN_SECONDARY }}">Cancel</a>
                            <button type="submit" class="{{ $ui::BTN_PRIMARY }}">Review &amp; save</button>
                        </div>
                    </div>

                    {{-- Review modal ("Approve AKSIC" pattern) --}}
                    <div x-show="review" x-cloak class="fixed inset-0 z-50" style="display: none;" @keydown.escape.window="review = false">
                        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="review = false"></div>
                        <div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto p-4">
                            <div class="relative w-full overflow-hidden rounded-lg bg-white text-left shadow-xl sm:max-w-lg" role="dialog" aria-modal="true" aria-labelledby="fc-review-title">
                                <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:size-10">
                                            <svg class="size-6 text-emerald-600" style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        </div>
                                        <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 id="fc-review-title" class="text-lg font-medium leading-6 text-black">Save this file?</h3>
                                            <div class="mt-3 rounded-md border border-gray-400 bg-gray-50 p-3">
                                                <dl class="space-y-1.5 text-sm">
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">Office</dt><dd class="text-right font-semibold text-black" x-text="officeLabel()"></dd></div>
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">Category</dt><dd class="text-right font-semibold text-black" x-text="categoryLabel()"></dd></div>
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">Document date</dt><dd class="text-right font-semibold text-black" x-text="date.split('-').reverse().join('.')"></dd></div>
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">Title</dt><dd class="text-right font-semibold text-black" x-text="title || '—'"></dd></div>
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">File no</dt><dd class="text-right font-semibold text-black" x-text="fileNo || '—'"></dd></div>
                                                    <div class="flex justify-between gap-4"><dt class="text-gray-700">Pages</dt><dd class="text-right font-semibold text-black" x-text="files.length + ' (' + size(totalSize()) + ')'"></dd></div>
                                                </dl>
                                            </div>
                                            <p class="mt-3 text-sm text-gray-700">You become the custodian. The pages are uploaded in the order shown.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 bg-gray-100 px-6 py-4">
                                    <button type="button" @click="review = false" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Cancel</button>
                                    <button type="button" @click="confirmSave()" :disabled="saving"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-700 disabled:opacity-60">
                                        <span x-show="!saving">Save file</span><span x-show="saving" x-cloak>Uploading…</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endunless
        </div>
    </div>

    @push('modals')
        <script>
            function fmsCreate(init) {
                const allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                const maxBytes = 10 * 1024 * 1024;

                return {
                    ...init,
                    category: document.getElementById('file_category_id')?.value || '',
                    date: document.getElementById('document_date')?.value || '',
                    title: document.getElementById('title')?.value || '',
                    fileNo: document.getElementById('file_no')?.value || '',
                    files: [],
                    over: false,
                    review: false,
                    saving: false,
                    pagesError: '',
                    add(list) {
                        [...list].forEach((file) => {
                            const ext = (file.name.split('.').pop() || '').toLowerCase();
                            let error = '';
                            if (! allowed.includes(ext)) error = 'Not allowed: use PDF, Word, JPG or PNG';
                            else if (file.size > maxBytes) error = 'Too large: ' + this.size(file.size) + ' (10 MB max)';
                            this.files.push({
                                key: file.name + file.size + Math.random(),
                                file, ext: ext.toUpperCase() || 'FILE', error,
                                preview: ! error && file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                            });
                        });
                        this.pagesError = '';
                    },
                    remove(i) {
                        const [f] = this.files.splice(i, 1);
                        if (f.preview) URL.revokeObjectURL(f.preview);
                    },
                    move(i, step) {
                        const j = i + step;
                        if (j < 0 || j >= this.files.length) return;
                        [this.files[i], this.files[j]] = [this.files[j], this.files[i]];
                    },
                    validFiles() { return this.files.filter((f) => ! f.error); },
                    totalSize() { return this.validFiles().reduce((sum, f) => sum + f.file.size, 0); },
                    size(bytes) {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB';
                        return (bytes / 1048576).toFixed(1) + ' MB';
                    },
                    categoryLabel() {
                        // Reads this.category so the summary updates when the category changes.
                        const el = document.getElementById('file_category_id');
                        const opt = el && this.category ? [...el.options].find((o) => o.value === this.category) : null;
                        return opt ? opt.text.trim() : '';
                    },
                    officeLabel() {
                        if (this.auto) return this.auto;
                        const o = (this.options[this.type] || []).find((x) => String(x.id) === String(this.officeId));
                        return o ? this.types[this.type] + ': ' + o.label : '';
                    },
                    openReview() {
                        const form = document.getElementById('fms-create');
                        if (! form.reportValidity()) return;
                        if (this.files.some((f) => f.error)) { this.pagesError = 'Remove the files marked in red first.'; return; }
                        if (! this.files.length) { this.pagesError = 'Add at least one scanned page.'; return; }
                        this.review = true;
                    },
                    confirmSave() {
                        if (this.saving) return;
                        const dt = new DataTransfer();
                        this.validFiles().forEach((f) => dt.items.add(f.file));
                        this.$refs.pages.files = dt.files;
                        this.saving = true;
                        document.getElementById('fms-create').submit();
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
