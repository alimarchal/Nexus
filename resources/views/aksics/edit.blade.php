@php
    /**
     * AKSIC -- edit screen.
     *
     * Wrapper only: the fields come from aksics._form and are unchanged. This
     * page adds the case context, inline validation summary and a sticky action
     * bar so a long form can be saved without scrolling back to the top.
     */
    $isLocked = $aksic->amortizations()->exists() && ! auth()->user()?->hasRole('super-admin');
    $btn = 'inline-flex items-center gap-1.5 rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit AKSIC Case {{ $aksic->application_no }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $aksic->name ?? '—' }}
                    @if ($aksic->cnic) &middot; CNIC {{ $aksic->cnic }} @endif
                    &middot; Status {{ $aksic->status ?? 'Pending' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('aksic.show', $aksic) }}"
                    class="{{ $btn }} bg-blue-950 text-white hover:bg-green-800 focus:ring-indigo-500">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Case
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">

            @if ($isLocked)
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                    A repayment schedule already exists for this case. Saving changes will not regenerate it.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-900/30">
                    <p class="text-sm font-bold text-red-800 dark:text-red-200">
                        {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} stopped this case from being saved:
                    </p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('aksic.update', $aksic) }}" id="aksic-edit-form">
                @csrf
                @method('PUT')

                @include('aksics._form')

                {{-- Sticky action bar: a long form stays saveable from anywhere. --}}
                <div class="sticky bottom-0 z-10 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white/95 px-5 py-3 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Fields marked <span class="font-bold text-red-600">*</span> are required.
                        Last updated {{ $aksic->updated_at?->format('d.m.Y H:i') ?? '—' }}
                        @if ($aksic->updater) by {{ $aksic->updater->name }} @endif
                    </p>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('aksic.show', $aksic) }}"
                            class="{{ $btn }} bg-gray-300 text-gray-800 hover:bg-gray-400 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                        <x-button class="bg-blue-950 hover:bg-green-800">Update AKSIC</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
