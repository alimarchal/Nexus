<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AccountOpeningWorkflowException;
use App\Models\AccountOpeningRequest;
use App\Services\AccountOpeningWorkflow;
use App\Support\PaperForm\PaperUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The specimen signatures, thumb impressions and documents uploaded on an old (paper) Account Opening Form. Each one
 * goes into a slot of the form, is stored on a private disk, is written to the request's history and cannot be changed
 * once the request is submitted or approved, the same as the form details.
 */
class PaperAccountOpeningUploadController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AccountOpeningWorkflow $workflow) {}

    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:edit account openings', only: ['store', 'destroy']),
            new Middleware('role_or_permission:view account openings', only: ['show']),
        ];
    }

    public function store(Request $request, AccountOpeningRequest $accountOpeningRequest): RedirectResponse|JsonResponse
    {
        $this->authorizeRequest($accountOpeningRequest);

        if ($blocked = $this->blockedFromEditing($accountOpeningRequest, $request)) {
            return $blocked;
        }

        $slots = PaperUploads::slots($accountOpeningRequest->paperForm?->values() ?? []);

        $validated = $request->validate([
            'slot' => ['required', 'string', Rule::in(array_keys($slots))],
            'file' => ['required', 'file', 'mimes:'.implode(',', PaperUploads::MIMES), 'max:'.PaperUploads::MAX_KILOBYTES],
        ], [
            'slot.in' => 'That upload does not belong on this form.',
            'file.mimes' => 'Upload a JPG, PNG or PDF file.',
            'file.max' => 'The file may not be larger than '.(PaperUploads::MAX_KILOBYTES / 1024).' MB.',
        ]);

        $slot = $validated['slot'];
        $previous = $this->inSlot($accountOpeningRequest, $slot);
        $file = $request->file('file');

        $stored = $accountOpeningRequest->addMedia($file)
            ->usingFileName(Str::uuid().'.'.$file->getClientOriginalExtension())
            ->withCustomProperties(['slot' => $slot, 'original_filename' => $file->getClientOriginalName()])
            ->toMediaCollection(PaperUploads::COLLECTION);

        $previous?->delete();

        $this->workflow->record($accountOpeningRequest, 'step_saved', $request->user(), 'paper', [
            $slots[$slot] => ['old' => $previous?->getCustomProperty('original_filename'), 'new' => $file->getClientOriginalName()],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Uploaded: '.$slots[$slot].'.',
                'upload' => [
                    'name' => $stored->getCustomProperty('original_filename', $stored->file_name),
                    'show_url' => route('account-openings.paper.uploads.show', [$accountOpeningRequest, $stored->id]),
                    'destroy_url' => route('account-openings.paper.uploads.destroy', [$accountOpeningRequest, $stored->id]),
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Uploaded: '.$slots[$slot].'.');
    }

    public function destroy(Request $request, AccountOpeningRequest $accountOpeningRequest, int $media): RedirectResponse|JsonResponse
    {
        $this->authorizeRequest($accountOpeningRequest);

        if ($blocked = $this->blockedFromEditing($accountOpeningRequest, $request)) {
            return $blocked;
        }

        $upload = $this->findUpload($accountOpeningRequest, $media);
        $label = PaperUploads::slots($accountOpeningRequest->paperForm?->values() ?? [])[$upload->getCustomProperty('slot')] ?? $upload->getCustomProperty('slot');

        $this->workflow->record($accountOpeningRequest, 'step_saved', $request->user(), 'paper', [
            $label => ['old' => $upload->getCustomProperty('original_filename'), 'new' => null],
        ]);
        $upload->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Upload removed: '.$label.'.']);
        }

        return redirect()->back()->with('success', 'Upload removed: '.$label.'.');
    }

    public function show(AccountOpeningRequest $accountOpeningRequest, int $media): StreamedResponse
    {
        $this->authorizeRequest($accountOpeningRequest);

        $upload = $this->findUpload($accountOpeningRequest, $media);

        return Storage::disk($upload->disk)->response($upload->getPathRelativeToRoot(), $upload->getCustomProperty('original_filename', $upload->file_name));
    }

    private function inSlot(AccountOpeningRequest $accountOpeningRequest, string $slot): ?Media
    {
        return $accountOpeningRequest->getMedia(PaperUploads::COLLECTION)->first(fn (Media $media): bool => $media->getCustomProperty('slot') === $slot);
    }

    private function findUpload(AccountOpeningRequest $accountOpeningRequest, int $media): Media
    {
        return Media::query()
            ->whereKey($media)
            ->where('model_type', $accountOpeningRequest->getMorphClass())
            ->where('model_id', $accountOpeningRequest->getKey())
            ->where('collection_name', PaperUploads::COLLECTION)
            ->firstOrFail();
    }

    private function authorizeRequest(AccountOpeningRequest $accountOpeningRequest): void
    {
        abort_unless(
            AccountOpeningRequest::visibleTo(auth()->user())->whereKey($accountOpeningRequest->id)->exists(),
            404
        );
        abort_unless($accountOpeningRequest->isOldEdition(), 404);
    }

    private function blockedFromEditing(AccountOpeningRequest $accountOpeningRequest, Request $request): RedirectResponse|JsonResponse|null
    {
        try {
            $this->workflow->assertEditable($accountOpeningRequest);
        } catch (AccountOpeningWorkflowException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return redirect()->route('account-openings.show', $accountOpeningRequest)->with('error', $exception->getMessage());
        }

        return null;
    }
}
