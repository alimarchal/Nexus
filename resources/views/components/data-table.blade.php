@props([
    'items' => [],
    'headers' => [],
    'emptyMessage' => 'No records found.',
    'emptyRoute' => null,
    'emptyLinkText' => 'Add a new record',
])

<div class="mx-auto max-w-7xl pb-16 sm:px-6 lg:px-8">
    <x-status-message />

    <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
        @if ($items->count() > 0)
            <div class="relative overflow-x-auto rounded-lg">
                <table class="w-full min-w-max table-auto text-sm">
                    <thead>
                        <tr class="bg-green-800 text-sm uppercase text-white">
                            @foreach ($headers as $header)
                                <th class="px-2 py-2 {{ $header['align'] ?? 'text-left' }}">
                                    {!! $header['label'] !!}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="text-md font-extrabold leading-normal text-black dark:text-gray-100">
                        {{ $slot }}
                    </tbody>
                    @isset($footer)
                        <tfoot class="bg-gray-100 text-sm font-bold uppercase dark:bg-gray-900">
                            {{ $footer }}
                        </tfoot>
                    @endisset
                </table>
            </div>

            @if (method_exists($items, 'hasPages') && $items->hasPages())
                <div class="px-2 py-2">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <p class="py-4 text-center text-gray-700 dark:text-gray-300">
                {{ $emptyMessage }}
                @if ($emptyRoute)
                    <a href="{{ $emptyRoute }}" class="text-blue-600 hover:underline">
                        {{ $emptyLinkText }}
                    </a>.
                @endif
            </p>
        @endif
    </div>
</div>
