<?php

use App\Models\Box;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * A file archived twice raised its box's file count each time, so some boxes show more files than they hold and
     * can look full too early. Set every box's count from the files really in it. Nothing else is changed: a box
     * closed by hand stays closed, and only a full/open status that no longer matches its count is corrected.
     */
    public function up(): void
    {
        Box::query()->orderBy('id')->chunkById(200, function ($boxes): void {
            foreach ($boxes as $box) {
                $box->refreshFileCount();
            }
        });
    }

    public function down(): void
    {
        // The earlier (wrong) counts cannot be restored and are not worth restoring.
    }
};
