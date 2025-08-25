<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">Edit Audit {{
            $audit->reference_no }}</h2>
        <div class="float-right space-x-2">
            <a href="{{ route('audits.show',$audit) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md">Back</a>
        </div>
    </x-slot>
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <form method="POST" action="{{ route('audits.update',$audit) }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium">Audit Type</label>
                        <select name="audit_type_id"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach($auditTypes as $t)
                            <option value="{{ $t->id }}" @selected(old('audit_type_id',$audit->
                                audit_type_id)==$t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Title</label>
                        <input type="text" name="title" value="{{ old('title',$audit->title) }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Lead Auditor</label>
                        <select name="lead_auditor_id"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- None --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('lead_auditor_id',$audit->
                                lead_auditor_id)==$u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Auditee User</label>
                        <select name="auditee_user_id"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- None --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('auditee_user_id',$audit->
                                auditee_user_id)==$u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Risk</label>
                        <select name="risk_overall"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- None --</option>
                            @foreach(['low','medium','high','critical'] as $r)
                            <option value="{{ $r }}" @selected(old('risk_overall',$audit->risk_overall)==$r)>{{
                                ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Status</label>
                        <select name="status"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach(['planned','in_progress','reporting','issued','closed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected(old('status',$audit->status)==$s)>{{
                                ucwords(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Tags</label>
                        <select name="tag_ids[]" multiple size="5"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(collect(old('tag_ids',$audit->
                                tags->pluck('id')->all()))->contains($tag->id))>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" rows="4"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('description',$audit->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Scope Summary</label>
                    <textarea name="scope_summary" rows="3"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('scope_summary',$audit->scope_summary) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Add Documents</label>
                    <input type="file" name="documents[]" multiple class="mt-1 w-full text-sm" />
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <a href="{{ route('audits.show',$audit) }}"
                        class="px-4 py-2 bg-gray-500 text-white text-xs font-semibold rounded-md">Cancel</a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md">Save</button>
                </div>
                <div class="border-t pt-4 mt-6">
                    <h4 class="font-semibold mb-2 text-sm">Quick Risk (optional)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium">Title</label>
                            <input type="text" name="risk[title]" value="{{ old('risk.title') }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium">Likelihood</label>
                            <select name="risk[likelihood]"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                @foreach(['low','medium','high','critical'] as $v)
                                <option value="{{ $v }}" @selected(old('risk.likelihood')==$v)>{{ ucfirst($v) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium">Impact</label>
                            <select name="risk[impact]"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                @foreach(['low','medium','high','critical'] as $v)
                                <option value="{{ $v }}" @selected(old('risk.impact')==$v)>{{ ucfirst($v) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="block text-xs font-medium">Description</label>
                        <textarea name="risk[description]" rows="2"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('risk.description') }}</textarea>
                    </div>
                </div>
                @if($checklistItems->count())
                <div class="border-t pt-4 mt-6">
                    <h4 class="font-semibold mb-2 text-sm">Checklist Items ({{ $checklistItems->count() }})</h4>
                    <ul class="text-xs space-y-1 max-h-40 overflow-y-auto pr-2">
                        @foreach($checklistItems as $ci)
                        <li class="border-b pb-1">{{ $ci->reference_code }} - {{ $ci->title }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </form>
        </div>
    </div>
</x-app-layout>