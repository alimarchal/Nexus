<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /**
     * Download file by path
     */
    public function download($path)
    {
        // Validate path for security
        if ($this->isUnsafePath($path)) {
            abort(403, 'Access denied');
        }

        // Check private storage first
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        // Check public storage
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        abort(404, 'File not found');
    }

    /**
     * View file inline
     */
    public function view($path)
    {
        // Validate path for security
        if ($this->isUnsafePath($path)) {
            abort(403, 'Access denied');
        }

        // Check private storage first
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        // Check public storage
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404, 'File not found');
    }

    /**
     * Check for unsafe path traversal attempts
     */
    private function isUnsafePath($path)
    {
        // Block path traversal attempts
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            return true;
        }

        // Allow all paths that don't contain traversal attempts
        return false;
    }
}