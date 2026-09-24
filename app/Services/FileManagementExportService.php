<?php

namespace App\Services;

use App\Models\FileManagementSystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Exports of the (filtered or selected) file list:
 *
 *   csv()  -> one row per file, for Excel
 *   zip()  -> one folder per file holding all its scanned pages / documents,
 *             plus 00-index.csv describing every folder
 *
 * Limits keep one download from loading the server; the list screen tells the
 * user to narrow the filter when they are exceeded.
 */
class FileManagementExportService
{
    public const MAX_ZIP_FILES = 300;

    public const MAX_ZIP_BYTES = 1024 * 1024 * 1024; // 1 GB of pages

    /** @var array<int, string> */
    private const CSV_HEADINGS = ['Digital ID', 'File No', 'Title', 'Category', 'Office Type', 'Office', 'Document Date', 'Status', 'Box', 'Position', 'Custodian', 'Pages', 'Added On', 'Added By'];

    public function csv(Builder $query, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Urdu / special characters
            fputcsv($out, self::CSV_HEADINGS);

            $query->with(['fileCategory:id,category_name', 'fileable', 'box:id,box_number', 'currentCustodian:id,name', 'creator:id,name'])
                ->withCount('media as pages_count')
                ->lazy(500)
                ->each(fn (FileManagementSystem $file) => fputcsv($out, $this->row($file)));

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Totals used to refuse a ZIP that is too large before building it.
     *
     * @return array{files: int, pages: int, bytes: int}
     */
    public function measure(Builder $query): array
    {
        $ids = (clone $query)->reorder()->select('file_management_systems.id');
        $media = Media::query()->where('model_type', (new FileManagementSystem)->getMorphClass())->whereIn('model_id', $ids);

        return [
            'files' => (clone $query)->count(),
            'pages' => (clone $media)->count(),
            'bytes' => (int) (clone $media)->sum('size'),
        ];
    }

    /**
     * Build the ZIP in storage/app/tmp and return its path; the caller sends it
     * and deletes it afterwards.
     *
     * @return array{path: string, name: string, files: int, pages: int, missing: int}
     */
    public function zip(Builder $query, string $label): array
    {
        $stamp = now()->format('Ymd-His');
        $root = $this->safe('FileManagement_'.$label.'_'.$stamp);
        $dir = storage_path('app/tmp');
        File::ensureDirectoryExists($dir);
        $path = $dir.'/'.Str::uuid().'.zip';

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the ZIP file.');
        }

        $index = [array_merge(['Folder'], self::CSV_HEADINGS, ['Files in folder'])];
        $files = 0;
        $pages = 0;
        $missing = 0;
        $usedFolders = [];

        $query->with(['fileCategory:id,category_name', 'fileable', 'box:id,box_number', 'currentCustodian:id,name', 'creator:id,name', 'media'])
            ->withCount('media as pages_count')
            ->lazy(100)
            ->each(function (FileManagementSystem $file) use ($zip, $root, &$index, &$files, &$pages, &$missing, &$usedFolders): void {
                $folder = $this->uniqueName($this->safe(trim($file->digital_id.' - '.($file->title ?: $file->file_no ?: 'Untitled'))), $usedFolders);
                $zip->addEmptyDir($root.'/'.$folder);
                $names = [];
                $listed = [];

                foreach ($file->media->sortBy('order_column')->values() as $i => $media) {
                    $original = $media->getCustomProperty('original_filename', $media->file_name);
                    $entry = $this->uniqueName(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT).' - '.$this->safe($original), $names);

                    if ($this->addMedia($zip, $media, $root.'/'.$folder.'/'.$entry)) {
                        $pages++;
                    } else {
                        $missing++;
                        $entry .= ' (missing on server, not included)';
                    }
                    $listed[] = $entry;
                }

                $index[] = array_merge([$folder], $this->row($file), [implode(' | ', $listed)]);
                $files++;
            });

        $zip->addFromString($root.'/00-index.csv', "\xEF\xBB\xBF".$this->toCsv($index));
        $zip->close();

        return ['path' => $path, 'name' => $root.'.zip', 'files' => $files, 'pages' => $pages, 'missing' => $missing];
    }

    private function addMedia(ZipArchive $zip, Media $media, string $entry): bool
    {
        $local = rescue(fn () => $media->getPath(), null, false);
        if ($local && is_file($local)) {
            return $zip->addFile($local, $entry);
        }

        // Non-local disk (e.g. S3): copy the bytes in.
        $disk = Storage::disk($media->disk);
        $relative = $media->getPathRelativeToRoot();

        return $disk->exists($relative) && $zip->addFromString($entry, (string) $disk->get($relative));
    }

    /**
     * @return array<int, string|int>
     */
    private function row(FileManagementSystem $file): array
    {
        return [
            $file->digital_id,
            $file->file_no,
            $file->title,
            $file->fileCategory?->category_name,
            Str::headline((string) $file->fileable_type),
            trim(($file->fileable?->code ?? '').' '.($file->fileable_name ?? '')),
            $file->document_date?->format('d.m.Y'),
            $file->is_archived ? 'Archived' : 'In circulation',
            $file->box?->box_number,
            $file->position_in_box,
            $file->currentCustodian?->name,
            (int) $file->pages_count,
            $file->created_at?->format('d.m.Y H:i'),
            $file->creator?->name,
        ];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /** File / folder name that is valid on Windows and macOS. */
    private function safe(string $name): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/u', '-', $name) ?? 'file';
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '', ' .-');

        return Str::limit($name !== '' ? $name : 'file', 120, '');
    }

    /**
     * @param  array<string, bool>  $used
     */
    private function uniqueName(string $name, array &$used): string
    {
        $candidate = $name;
        $n = 2;
        while (isset($used[mb_strtolower($candidate)])) {
            $info = pathinfo($name);
            $candidate = isset($info['extension'])
                ? $info['filename'].' ('.$n.').'.$info['extension']
                : $name.' ('.$n.')';
            $n++;
        }
        $used[mb_strtolower($candidate)] = true;

        return $candidate;
    }

    public static function humanBytes(int $bytes): string
    {
        return $bytes >= 1073741824 ? number_format($bytes / 1073741824, 1).' GB'
            : ($bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format(max(1, $bytes / 1024)).' KB');
    }
}
