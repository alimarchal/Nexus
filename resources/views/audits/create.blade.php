<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">Create New Audit
        </h2>
        <div class="flex justify-center items-center float-right">
            <a href="{{ route('audits.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-status-message />
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('audits.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if ($errors->any())
                    <div class="alert alert-danger mb-4 p-4 rounded bg-red-100 text-red-700">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Audit Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Audit Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700">Audit Type <span
                                        class="text-red-600">*</span>:</label>
                                <select name="audit_type_id" required
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- Select Type --</option>
                                    @foreach($auditTypes as $t)
                                    <option value="{{ $t->id }}" @selected(old('audit_type_id')==$t->id)>{{ $t->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('audit_type_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Title <span class="text-red-600">*</span>:</label>
                                <input type="text" name="title" value="{{ old('title') }}" required
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                @error('title')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Lead Auditor <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="lead_auditor_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- Select Lead Auditor --</option>
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected(old('lead_auditor_id')==$u->id)>{{ $u->name
                                        }}</option>
                                    @endforeach
                                </select>
                                @error('lead_auditor_id')<span class="text-red-500 text-sm">{{ $message
                                    }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Auditee (Primary Contact) <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="auditee_user_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- Select Auditee --</option>
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected(old('auditee_user_id')==$u->id)>{{ $u->name
                                        }}</option>
                                    @endforeach
                                </select>
                                @error('auditee_user_id')<span class="text-red-500 text-sm">{{ $message
                                    }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Planned Start <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="date" name="planned_start_date" value="{{ old('planned_start_date') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                @error('planned_start_date')<span class="text-red-500 text-sm">{{ $message
                                    }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Planned End <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="date" name="planned_end_date" value="{{ old('planned_end_date') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                @error('planned_end_date')<span class="text-red-500 text-sm">{{ $message
                                    }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Risk Overall <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="risk_overall"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- Select Risk --</option>
                                    @foreach(['low','medium','high','critical'] as $r)
                                    <option value="{{ $r }}" @selected(old('risk_overall')==$r)>{{ ucfirst($r) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('risk_overall')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-gray-700">Parent Audit <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="parent_audit_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- None --</option>
                                    @foreach($parentAudits as $pa)
                                    <option value="{{ $pa->id }}" @selected(old('parent_audit_id')==$pa->id)> {{
                                        $pa->reference_no }} - {{ Str::limit($pa->title,30) }}</option>
                                    @endforeach
                                </select>
                                @error('parent_audit_id')<span class="text-red-500 text-sm">{{ $message
                                    }}</span>@enderror
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Description <span
                                    class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                            <textarea name="description" rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('description') }}</textarea>
                            @error('description')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Scope Summary <span
                                    class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                            <textarea name="scope_summary" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('scope_summary') }}</textarea>
                            @error('scope_summary')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <!-- Assignment & Tags Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Assignment & Tags</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="md:col-span-1">
                                <label class="block text-gray-700">Tags <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <select name="tag_ids[]" multiple size="5"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" @selected(collect(old('tag_ids',[]))->
                                        contains($tag->id))>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-gray-600">Hold Ctrl/Cmd to select multiple</small>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-gray-700">Documents (max 10) <span
                                        class="text-gray-500 text-xs font-normal">(Optional)</span>:</label>
                                <input type="file" name="documents[]" multiple
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                @error('documents')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                                @error('documents.*')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Quick Risk Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Quick Risk <span
                                class="text-gray-500 text-xs font-normal">(Optional)</span></h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-xs">Title</label>
                                <input type="text" name="risk[title]" value="{{ old('risk.title') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" />
                            </div>
                            <div>
                                <label class="block text-gray-700 text-xs">Likelihood</label>
                                <select name="risk[likelihood]"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @foreach(['low','medium','high','critical'] as $v)
                                    <option value="{{ $v }}" @selected(old('risk.likelihood')==$v)> {{ ucfirst($v) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-xs">Impact</label>
                                <select name="risk[impact]"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @foreach(['low','medium','high','critical'] as $v)
                                    <option value="{{ $v }}" @selected(old('risk.impact')==$v)> {{ ucfirst($v) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-xs">Description</label>
                            <textarea name="risk[description]" rows="2"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('risk.description') }}</textarea>
                        </div>
                    </div>

                    <!-- Checklist Preview Section -->
                    @if($checklistItems->count())
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Checklist Preview ({{
                            $checklistItems->count() }})</h3>
                        <ul class="text-xs space-y-1 max-h-40 overflow-y-auto pr-2 border rounded p-3 bg-gray-50">
                            @foreach($checklistItems as $ci)
                            <li class="border-b pb-1 last:border-b-0">{{ $ci->reference_code }} - {{ $ci->title }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md">Create Audit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>