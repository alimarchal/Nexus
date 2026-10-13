{{-- The official Terms & Conditions and Indemnity & Undertaking text (config/paper_form.php). --}}
@php
    $terms = config('paper_form.terms', []);
    $indemnity = config('paper_form.indemnity', '');
@endphp

<div class="paper-terms md:col-span-2" data-terms>
    @if ($terms === [] && $indemnity === '')
        <p class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900" data-terms-missing>
            The official Terms &amp; Conditions and Indemnity &amp; Undertaking text has not been loaded yet. Add it to
            <code>resources/paper-form/terms.txt</code> and <code>resources/paper-form/indemnity.txt</code>.
        </p>
    @else
        <div class="{{ ($printing ?? false) ? '' : 'max-h-72 overflow-y-auto' }} rounded-md border border-gray-300 p-3 text-xs leading-relaxed">
            @if ($terms !== [])
                <h4 class="mb-1 font-bold">Terms &amp; Conditions</h4>
                <ol class="list-decimal space-y-1 pl-5">
                    @foreach ($terms as $clause)
                        <li>{{ $clause }}</li>
                    @endforeach
                </ol>
            @endif
            @if ($indemnity !== '')
                <h4 class="mb-1 mt-3 font-bold">Indemnity &amp; Undertaking</h4>
                <p class="whitespace-pre-line">{{ $indemnity }}</p>
            @endif
        </div>
    @endif
</div>
