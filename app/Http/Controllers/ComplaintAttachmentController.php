<?php

namespace App\Http\Controllers;

use App\Models\ComplaintAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ComplaintAttachmentController extends Controller
{
    /**
     * Download the specified attachment.
     */
    public function download($id)
    {
        $attachment = ComplaintAttachment::findOrFail($id);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download(storage_path("app/public/{$attachment->file_path}"), $attachment->original_filename);
    }
}