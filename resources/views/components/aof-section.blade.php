@props(['title', 'subtitle' => null, 'reference' => null])

<section {{ $attributes->merge(['class' => 'mb-8 border-b border-gray-200 pb-6 last:mb-0 last:border-0 last:pb-0 dark:border-gray-700']) }}>
    <header class="mb-4">
        <h3 class="text-base font-bold uppercase tracking-wide text-green-800 dark:text-green-400">
            {{ $title }}
        </h3>
        @if ($subtitle)
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
        @if ($reference)
            <p class="text-[11px] italic text-gray-400">Form reference: {{ $reference }}</p>
        @endif
    </header>

    {{ $slot }}
</section>
