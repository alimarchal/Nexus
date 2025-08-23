<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">New Audit</h2>
        <div class="float-right space-x-2">
            <a href="{{ route('audits.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md">Back</a>
        </div>
    </x-slot>
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('audits.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Audit Type <span
                                class="text-red-500">*</span></label>
                        <select name="audit_type_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- Select Type --</option>
                            @foreach($auditTypes as $t)
                            <option value="{{ $t->id }}" @selected(old('audit_type_id')==$t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('audit_type_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lead Auditor</label>
                        <select name="lead_auditor_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- Select Lead Auditor --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('lead_auditor_id')==$u->id)>{{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('lead_auditor_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auditee (Primary
                            Contact)</label>
                        <select name="auditee_user_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- Select Auditee --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('auditee_user_id')==$u->id)>{{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('auditee_user_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned Start</label>
                        <input type="date" name="planned_start_date" value="{{ old('planned_start_date') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        @error('planned_start_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Planned End</label>
                        <input type="date" name="planned_end_date" value="{{ old('planned_end_date') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        @error('planned_end_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Risk Overall</label>
                        <select name="risk_overall"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- Select Risk --</option>
                            @foreach(['low','medium','high','critical'] as $r)
                            <option value="{{ $r }}" @selected(old('risk_overall')==$r)>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        @error('risk_overall')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Audit</label>
                        <select name="parent_audit_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- None --</option>
                            @foreach($parentAudits as $pa)
                            <option value="{{ $pa->id }}" @selected(old('parent_audit_id')==$pa->id)>{{
                                $pa->reference_no }} - {{ Str::limit($pa->title,30) }}</option>
                            @endforeach
                        </select>
                        @error('parent_audit_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('description') }}</textarea>
                    @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scope Summary</label>
                    <textarea name="scope_summary" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('scope_summary') }}</textarea>
                    @error('scope_summary')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Documents (max 10)</label>
                    <input type="file" name="documents[]" multiple
                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-200" />
                    @error('documents')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    @error('documents.*')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="pt-4 flex justify-end space-x-4">
                    <a href="{{ route('audits.index') }}"
                        class="px-4 py-2 bg-gray-500 text-white text-xs font-semibold rounded-md">Cancel</a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md">Create
                        Audit</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>