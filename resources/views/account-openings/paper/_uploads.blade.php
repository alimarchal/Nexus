{{-- The signatures, thumb impressions and documents uploaded on the old form. Needs $accountOpeningRequest and $values; $printing for the print. --}}
@php
    $uploadSlots = \App\Support\PaperForm\PaperUploads::slots($values);
    $uploads = $accountOpeningRequest->getMedia(\App\Support\PaperForm\PaperUploads::COLLECTION)
        ->keyBy(fn ($media) => $media->getCustomProperty('slot'));
    $groups = [
        'Specimen signatures & thumb impressions' => \App\Support\PaperForm\PaperUploads::signatureSlots($values),
        'Documents obtained' => \App\Support\PaperForm\PaperUploads::documentSlots($values),
    ];
    $uploadUrl = fn ($media) => route('account-openings.paper.uploads.show', [$accountOpeningRequest, $media->id]);
@endphp

@if ($uploads->isNotEmpty())
    <section class="paper-section mb-6" data-section="uploads">
        <h3 class="mb-3 text-base font-bold uppercase tracking-wide text-green-800">Uploaded signatures and documents</h3>
        @foreach ($groups as $heading => $slots)
            @php $filled = collect($slots)->filter(fn ($label, $slot) => $uploads->has($slot)); @endphp
            @if ($filled->isNotEmpty())
                <h4 class="mb-2 mt-3 text-sm font-semibold text-gray-700">{{ $heading }}</h4>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    @foreach ($filled as $slot => $label)
                        @php $media = $uploads[$slot]; @endphp
                        <figure class="rounded-md border border-gray-200 p-2 text-xs" style="page-break-inside: avoid">
                            <a href="{{ $uploadUrl($media) }}" target="_blank" rel="noopener">
                                @if (str_starts_with((string) $media->mime_type, 'image/'))
                                    <img src="{{ $uploadUrl($media) }}" alt="{{ $label }}" class="mb-1 h-24 w-full object-contain">
                                @else
                                    <div class="mb-1 flex h-24 items-center justify-center bg-gray-100 text-sm font-semibold text-gray-500">PDF</div>
                                @endif
                            </a>
                            <figcaption><b>{{ $label }}</b><br><span class="text-gray-500">{{ $media->getCustomProperty('original_filename', $media->file_name) }}</span></figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        @endforeach
    </section>
@endif
