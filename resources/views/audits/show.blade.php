<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">Audit {{
            $audit->reference_no }}</h2>
        <div class="float-right space-x-2">
            <a href="{{ route('audits.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md">Back</a>
            <a href="{{ route('audits.edit',$audit) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md">Edit</a>
        </div>
    </x-slot>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 py-6 space-y-6">
        <x-status-message />
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div><span class="font-semibold">Title:</span> {{ $audit->title }}</div>
                <div><span class="font-semibold">Type:</span> {{ $audit->type?->name ?? '-' }}</div>
                <div><span class="font-semibold">Status:</span> <span
                        class="px-2 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold">{{
                        ucwords(str_replace('_',' ',$audit->status)) }}</span></div>
                <div><span class="font-semibold">Risk:</span> {{ $audit->risk_overall ? ucfirst($audit->risk_overall) :
                    '-' }}</div>
                <div><span class="font-semibold">Lead Auditor:</span> {{ $audit->leadAuditor?->name ?? '-' }}</div>
                <div><span class="font-semibold">Auditee:</span> {{ $audit->auditeeUser?->name ?? '-' }}</div>
                <div><span class="font-semibold">Planned:</span> {{ $audit->planned_start_date?->format('Y-m-d') }} {{
                    $audit->planned_end_date? ' - '.$audit->planned_end_date->format('Y-m-d') : '' }}</div>
                <div><span class="font-semibold">Actual:</span> {{ $audit->actual_start_date?->format('Y-m-d') }} {{
                    $audit->actual_end_date? ' - '.$audit->actual_end_date->format('Y-m-d') : '' }}</div>
                <div><span class="font-semibold">Score:</span> {{ $audit->score ?? '-' }}</div>
                <div class="md:col-span-3 mt-2">
                    <span class="font-semibold">Tags:</span>
                    @forelse($audit->tags as $tag)
                    <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">{{ $tag->name
                        }}</span>
                    @empty
                    <span class="text-gray-500">—</span>
                    @endforelse
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-semibold mb-1">Description</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $audit->description ?: '—' }}
                </p>
            </div>
            <div class="mt-4">
                <h4 class="font-semibold mb-1">Scope Summary</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $audit->scope_summary ?: '—'
                    }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Update Audit</h3>
                <form method="POST" action="{{ route('audits.update',$audit) }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium">Status</label>
                        <select name="status"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach(['planned','in_progress','reporting','issued','closed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected($audit->status===$s)>{{ ucwords(str_replace('_',' ',$s))
                                }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Risk Overall</label>
                        <select name="risk_overall"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">-- None --</option>
                            @foreach(['low','medium','high','critical'] as $r)
                            <option value="{{ $r }}" @selected($audit->risk_overall===$r)>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Planned Dates</label>
                        <div class="flex space-x-2">
                            <input type="date" name="planned_start_date"
                                value="{{ $audit->planned_start_date?->format('Y-m-d') }}"
                                class="w-1/2 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                            <input type="date" name="planned_end_date"
                                value="{{ $audit->planned_end_date?->format('Y-m-d') }}"
                                class="w-1/2 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Documents (add)</label>
                        <input type="file" name="documents[]" multiple class="mt-1 w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Tags</label>
                        <select name="tag_ids[]" multiple size="5"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach($availableTags as $tag)
                            <option value="{{ $tag->id }}" @selected($audit->tags->pluck('id')->contains($tag->id))>{{
                                $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-semibold mb-2">Quick Risk</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-medium">Title</label>
                                <input type="text" name="risk[title]"
                                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block font-medium">Likelihood</label>
                                <select name="risk[likelihood]"
                                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                    @foreach(['low','medium','high','critical'] as $v)
                                    <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium">Impact</label>
                                <select name="risk[impact]"
                                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                    @foreach(['low','medium','high','critical'] as $v)
                                    <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="block text-xs font-medium">Description</label>
                            <textarea name="risk[description]" rows="2"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md">Save
                            Changes</button>
                    </div>
                </form>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Documents</h3>
                @if($audit->documents->count())
                <ul class="text-sm space-y-2">
                    @foreach($audit->documents as $doc)
                    <li class="flex justify-between items-center border-b pb-1">
                        <div>
                            <span class="font-medium">{{ $doc->original_name }}</span>
                            <span class="text-xs text-gray-500">({{ number_format($doc->size_bytes/1024,1) }} KB)</span>
                            <span class="block text-xs text-gray-400">{{ $doc->uploaded_at?->format('Y-m-d H:i')
                                }}</span>
                        </div>
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $doc->category }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-sm text-gray-500">No documents uploaded.</p>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Risks ({{ $audit->risks->count() }})</h3>
                @if($audit->risks->count())
                <ul class="text-sm space-y-2">
                    @foreach($audit->risks as $risk)
                    <li class="border-b pb-1">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ $risk->title }}</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">{{
                                ucfirst($risk->risk_level) }}</span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">L: {{ ucfirst($risk->likelihood) }} | I:
                            {{ ucfirst($risk->impact) }} | Status: {{ ucfirst($risk->status) }}</div>
                        @if($risk->description)
                        <p class="text-xs mt-1 text-gray-700 dark:text-gray-300">{{ $risk->description }}</p>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-sm text-gray-500">No risks recorded.</p>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Checklist Items ({{ $checklistItems->count() }})</h3>
                @if($checklistItems->count())
                <div class="max-h-64 overflow-y-auto text-xs">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="px-2 py-1 text-left">Ref</th>
                                <th class="px-2 py-1 text-left">Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklistItems as $ci)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-2 py-1 whitespace-nowrap">{{ $ci->reference_code }}</td>
                                <td class="px-2 py-1">{{ $ci->title }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500">No checklist items for this type.</p>
                @endif
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Status History</h3>
            @if($statusHistory->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <th class="px-3 py-2 text-left">Changed At</th>
                            <th class="px-3 py-2 text-left">From</th>
                            <th class="px-3 py-2 text-left">To</th>
                            <th class="px-3 py-2 text-left">By</th>
                            <th class="px-3 py-2 text-left">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statusHistory as $h)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ $h->changed_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2">{{ $h->from_status ? ucwords(str_replace('_',' ',$h->from_status)) :
                                '—' }}</td>
                            <td class="px-3 py-2 font-semibold">{{ ucwords(str_replace('_',' ',$h->to_status)) }}</td>
                            <td class="px-3 py-2">{{ $h->changer?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $h->note ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500">No history records.</p>
            @endif
        </div>
    </div>
</x-app-layout>