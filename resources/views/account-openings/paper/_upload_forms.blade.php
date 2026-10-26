{{-- Upload and remove the signatures, thumb impressions and documents of the old form (each is its own small form). --}}
@php
    $uploads = $accountOpeningRequest->getMedia(\App\Support\PaperForm\PaperUploads::COLLECTION)
        ->keyBy(fn ($media) => $media->getCustomProperty('slot'));
    $groups = [
        'Specimen signatures & thumb impressions' => \App\Support\PaperForm\PaperUploads::signatureSlots($values),
        'Documents/Papers to be Obtained' => \App\Support\PaperForm\PaperUploads::documentSlots($values),
    ];
@endphp

<div class="mt-6 space-y-6 overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg" id="paper-uploads" data-uploads>
    <div>
        <h3 class="text-base font-bold uppercase tracking-wide text-green-800">Uploads</h3>
        <p class="text-xs text-gray-600">Upload a JPG, PNG or PDF (up to {{ \App\Support\PaperForm\PaperUploads::MAX_KILOBYTES / 1024 }} MB) for each signature, thumb impression and document. A new upload replaces the old one in the same place. The list follows the form above: save the applicant names and the nature of account first.</p>
    </div>

    @foreach ($groups as $heading => $slots)
        <div>
            <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ $heading }}</h4>
            <ul class="divide-y divide-gray-100">
                @foreach ($slots as $slot => $label)
                    @php $media = $uploads->get($slot); @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 py-2 text-sm" data-slot="{{ $slot }}">
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-800">{{ $label }}</div>
                            @if ($media)
                                <a href="{{ route('account-openings.paper.uploads.show', [$accountOpeningRequest, $media->id]) }}" target="_blank" rel="noopener" class="text-xs text-blue-700 underline">{{ $media->getCustomProperty('original_filename', $media->file_name) }}</a>
                            @else
                                <span class="text-xs text-gray-400">Nothing uploaded</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('account-openings.paper.uploads.store', $accountOpeningRequest) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="slot" value="{{ $slot }}">
                            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required class="w-56 text-xs" aria-label="File for {{ $label }}">
                            <button class="rounded-md bg-blue-950 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-green-800">{{ $media ? 'Replace' : 'Upload' }}</button>
                        </form>
                        @if ($media)
                            <form method="POST" action="{{ route('account-openings.paper.uploads.destroy', [$accountOpeningRequest, $media->id]) }}" onsubmit="return confirm('Remove this upload?')">
                                @csrf @method('DELETE')
                                <button class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-red-700 hover:bg-red-50">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
