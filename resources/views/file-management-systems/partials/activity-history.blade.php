<hr class="my-6 border-gray-200 dark:border-gray-700">

<h3 class="mb-3 font-semibold text-gray-700 dark:text-gray-300">File History</h3>

@if ($activityHistory->isNotEmpty())
    <div class="overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Date &amp; Time</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Action</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Performed By</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @foreach ($activityHistory as $activity)
                    <tr>
                        <td class="whitespace-nowrap px-3 py-2 text-gray-700 dark:text-gray-300">
                            {{ $activity->created_at?->format('d-m-Y h:i A') }}
                        </td>
                        <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200">
                            {{ \Illuminate\Support\Str::headline($activity->event ?? 'updated') }}
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            {{ $activity->causer?->name ?? 'System' }}
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            @if ($activity->event === 'transferred')
                                {{ \Illuminate\Support\Str::headline((string) data_get($activity->properties, 'from.type')) }}
                                #{{ data_get($activity->properties, 'from.id') }}
                                to
                                {{ \Illuminate\Support\Str::headline((string) data_get($activity->properties, 'to.type')) }}
                                #{{ data_get($activity->properties, 'to.id') }}
                            @elseif (in_array($activity->event, ['page_uploaded', 'page_removed']))
                                {{ data_get($activity->properties, 'original_filename', $activity->description) }}
                            @else
                                {{ $activity->description }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">No history is available for this record.</p>
@endif