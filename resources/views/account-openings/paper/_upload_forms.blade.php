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
                    <li class="flex flex-wrap items-center justify-between gap-3 py-2 text-sm" data-slot="{{ $slot }}"
                        x-data="paperUpload({
                            upload: @js($media ? [
                                'name' => $media->getCustomProperty('original_filename', $media->file_name),
                                'show_url' => route('account-openings.paper.uploads.show', [$accountOpeningRequest, $media->id]),
                                'destroy_url' => route('account-openings.paper.uploads.destroy', [$accountOpeningRequest, $media->id]),
                            ] : null),
                        })">
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-800">{{ $label }}</div>
                            <a x-show="upload" x-cloak :href="upload?.show_url" x-text="upload?.name" target="_blank" rel="noopener" class="text-xs text-blue-700 underline">{{ $media?->getCustomProperty('original_filename', $media->file_name) }}</a>
                            <span x-show="! upload" @if ($media) x-cloak @endif class="text-xs text-gray-400">Nothing uploaded</span>
                            <p x-show="message" x-cloak x-text="message" class="text-xs" :class="failed ? 'text-red-600' : 'text-green-700'" role="status"></p>
                        </div>
                        {{-- Uploaded in place: the page is not reloaded, so what is typed in the form above and not yet saved stays. --}}
                        <form method="POST" action="{{ route('account-openings.paper.uploads.store', $accountOpeningRequest) }}" enctype="multipart/form-data" class="flex items-center gap-2"
                            @submit.prevent="send($event.target)">
                            @csrf
                            <input type="hidden" name="slot" value="{{ $slot }}">
                            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required class="w-56 text-xs" aria-label="File for {{ $label }}">
                            <button type="submit" :disabled="busy" class="rounded-md bg-blue-950 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-green-800 disabled:opacity-50" x-text="upload ? 'Replace' : 'Upload'">{{ $media ? 'Replace' : 'Upload' }}</button>
                        </form>
                        <form x-show="upload" @if (! $media) x-cloak @endif method="POST" action="{{ $media ? route('account-openings.paper.uploads.destroy', [$accountOpeningRequest, $media->id]) : '#' }}"
                            @submit.prevent="remove($event.target)">
                            @csrf @method('DELETE')
                            <button type="submit" :disabled="busy" class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-red-700 hover:bg-red-50 disabled:opacity-50">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>

<script>
    function paperUpload(config) {
        return {
            upload: config.upload,
            busy: false,
            message: '',
            failed: false,
            async call(url, body) {
                this.busy = true;
                this.message = '';
                try {
                    const response = await fetch(url, { method: 'POST', body, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const payload = await response.json().catch(() => ({}));
                    this.failed = ! response.ok;
                    this.message = response.ok ? payload.message : (payload.errors ? Object.values(payload.errors).flat().join(' ') : (payload.message || 'The upload failed. Try again.'));

                    return response.ok ? payload : null;
                } catch (error) {
                    this.failed = true;
                    this.message = 'The upload failed. Check your connection and try again.';

                    return null;
                } finally {
                    this.busy = false;
                }
            },
            async send(form) {
                const payload = await this.call(form.action, new FormData(form));
                if (payload) {
                    this.upload = payload.upload;
                    form.reset();
                }
            },
            async remove(form) {
                if (! confirm('Remove this upload?')) {
                    return;
                }
                const body = new FormData();
                body.append('_token', form.querySelector('input[name=_token]').value);
                body.append('_method', 'DELETE');
                const payload = await this.call(this.upload.destroy_url, body);
                if (payload) {
                    this.upload = null;
                }
            },
        };
    }
</script>
