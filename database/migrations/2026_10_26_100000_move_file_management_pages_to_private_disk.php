<?php

use App\Models\FileManagementSystem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    /**
     * Scanned pages used to be saved on the public disk, where anyone with the /storage/... link could open them
     * without logging in. Move every existing page to the private pages disk. A page whose file cannot be found
     * is left untouched (and logged) so nothing is ever lost, and the migration can be run again safely.
     */
    public function up(): void
    {
        $this->move('public', (string) config('file_management.pages_disk', 'local'));
    }

    public function down(): void
    {
        $this->move((string) config('file_management.pages_disk', 'local'), 'public');
    }

    private function move(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        Media::query()
            ->where('model_type', (new FileManagementSystem)->getMorphClass())
            ->where('collection_name', 'pages')
            ->where('disk', $from)
            ->orderBy('id')
            ->chunkById(200, function ($pages) use ($from, $to): void {
                foreach ($pages as $page) {
                    $record = FileManagementSystem::withTrashed()->find($page->model_id);

                    if ($record === null) {
                        continue;
                    }

                    $page->setRelation('model', $record);
                    $path = $page->getPathRelativeToRoot();

                    if (! Storage::disk($from)->exists($path)) {
                        Log::warning('File management page file not found, left on its current disk.', ['media_id' => $page->id, 'path' => $path, 'disk' => $from]);

                        continue;
                    }

                    if (! Storage::disk($to)->exists($path)) {
                        $stream = Storage::disk($from)->readStream($path);
                        Storage::disk($to)->writeStream($path, $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }

                    $page->forceFill(['disk' => $to, 'conversions_disk' => $to])->save();
                    Storage::disk($from)->delete($path);
                }
            });
    }
};
