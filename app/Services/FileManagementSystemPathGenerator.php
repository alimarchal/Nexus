<?php

namespace App\Services;

use App\Models\FileManagementSystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores FileManagementSystem media under a folder per org unit, then per digital ID,
 * e.g. `Branch/11/0011-20260828-0002/`, `Region/Kotli/...`, `President Office/...`.
 * Other models fall back to the package default (flat `{media-id}/`) layout.
 */
class FileManagementSystemPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media).'/responsive-images/';
    }

    private function basePath(Media $media): string
    {
        $model = $media->model;

        if (! $model instanceof FileManagementSystem) {
            return app(DefaultPathGenerator::class)->getPath($media);
        }

        return $this->orgFolder($model).'/'.$this->sanitize($model->digital_id);
    }

    private function orgFolder(FileManagementSystem $model): string
    {
        return match ($model->fileable_type) {
            'branch' => 'Branch/'.$this->sanitize((string) $model->fileable_id),
            'region' => 'Region/'.$this->sanitize($model->fileable?->name ?? (string) $model->fileable_id),
            'division' => 'Division/'.$this->sanitize($model->fileable?->name ?? (string) $model->fileable_id),
            default => 'President Office',
        };
    }

    /**
     * Keep folder names filesystem/URL safe without discarding readability.
     */
    private function sanitize(string $value): string
    {
        return trim(str_replace(['/', '\\', '..'], '-', $value), '-');
    }
}
