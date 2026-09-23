@props([
    'items' => [],
    'headers' => [],
    'emptyMessage' => 'No records found.',
    'emptyRoute' => null,
    'emptyLinkText' => 'Add a new record',
    // 'default' keeps the original green table; 'grid' is the Word-style
    // table (black header row, white text, black borders, white body).
    'variant' => 'default',
    // Optional line shown above the table, e.g. "Showing 1-25 of 87 branches".
    'caption' => null,
])

@php($grid = $variant === 'grid')

<div class="mx-auto max-w-7xl pb-16 sm:px-6 lg:px-8">
    <x-status-message />

    @if ($grid)
        @include('aksics._grid-style')
    @endif

    <div class="{{ $grid ? 'overflow-hidden rounded-lg border border-gray-400 bg-white shadow-md dark:border-gray-600 dark:bg-gray-800' : 'overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg' }}">
        @if ($items->count() > 0)
            @if ($grid && method_exists($items, 'total'))
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-400 bg-gray-100 px-4 py-2 text-xs text-black dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-200">
                    <span>{{ $caption ?? 'Records' }}</span>
                    <span>Showing <b>{{ $items->firstItem() }}</b>&ndash;<b>{{ $items->lastItem() }}</b> of <b>{{ $items->total() }}</b></span>
                </div>
            @endif
            <div class="{{ $grid ? 'relative overflow-x-auto p-3' : 'relative overflow-x-auto rounded-lg' }}">
                <table class="{{ $grid ? 'aksic-grid aksic-hover' : 'w-full min-w-max table-auto text-sm' }}">
                    <thead>
                        <tr class="{{ $grid ? '' : 'bg-green-800 text-sm uppercase text-white' }}">
                            @foreach ($headers as $header)
                                <th class="{{ $grid ? '' : 'px-2 py-2' }} {{ $header['align'] ?? 'text-left' }}" @if (! empty($header['width'])) style="width: {{ $header['width'] }}" @endif>
                                    {!! $header['label'] !!}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="{{ $grid ? '' : 'text-md font-extrabold leading-normal text-black dark:text-gray-100' }}">
                        {{ $slot }}
                    </tbody>
                    @isset($footer)
                        <tfoot class="{{ $grid ? '' : 'bg-gray-100 text-sm font-bold uppercase dark:bg-gray-900' }}">
                            {{ $footer }}
                        </tfoot>
                    @endisset
                </table>
            </div>

            @if (method_exists($items, 'hasPages') && $items->hasPages())
                <div class="{{ $grid ? 'border-t border-gray-400 px-4 py-3' : 'px-2 py-2' }}">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <p class="py-6 text-center text-black dark:text-gray-300">
                {{ $emptyMessage }}
                @if ($emptyRoute)
                    <a href="{{ $emptyRoute }}" class="font-semibold text-blue-700 hover:underline">
                        {{ $emptyLinkText }}
                    </a>.
                @endif
            </p>
        @endif
    </div>
</div>
