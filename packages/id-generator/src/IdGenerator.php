<?php

namespace Alimarchal\IdGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IdGenerator
{
    /**
     * Generate a unique ID with prefix, date, and serial number
     * Format: PREFIX-YYYYMMDD-XXXX
     *
     * @param  string  $type  The type from id_prefixes table (e.g., 'invoice', 'complaint')
     * @param  string  $targetTable  The table where the ID will be stored
     * @param  string  $targetColumn  The column name for the ID
     * @return string Generated unique ID
     *
     * @throws \Exception If prefix not found
     */
    public function generate(string $type, string $targetTable, string $targetColumn): string
    {
        try {
            return DB::transaction(function () use ($type, $targetTable, $targetColumn) {

                // 1. Get prefix from database
                $idPrefix = DB::table('id_prefixes')->where('name', $type)->first();
                if (! $idPrefix) {
                    throw new \Exception("Prefix for type '{$type}' not found in id_prefixes table.");
                }

                $prefix = $idPrefix->prefix;

                // 2. Create base ID with today's date
                $today = now()->format('Ymd');
                $baseId = $prefix.'-'.$today.'-';

                // 3. Find last serial number (with row locking to prevent race conditions)
                $lastRecord = DB::table($targetTable)
                    ->where($targetColumn, 'LIKE', $baseId.'%')
                    ->lockForUpdate() // Critical: prevents race conditions
                    ->latest($targetColumn)
                    ->first();

                $serial = 1;
                if ($lastRecord) {
                    $lastSerial = (int) substr($lastRecord->$targetColumn, -4);
                    $serial = $lastSerial + 1;
                }

                // 4. Format new ID with zero-padded serial
                $newSerial = str_pad($serial, 4, '0', STR_PAD_LEFT);

                return $baseId.$newSerial;

            }, 3); // Retry up to 3 times on deadlock

        } catch (\Throwable $e) {
            Log::error('Unique ID Generation Failed: '.$e->getMessage());

            // Fallback ID (less pretty but functional)
            return strtoupper($type).'-'.time().'-'.rand(1000, 9999);
        }
    }

    /**
     * Generate ID using direct prefix (without database lookup)
     *
     * @param  string  $prefix  Direct prefix (e.g., 'INV', 'CMP')
     * @param  string  $targetTable  The table where the ID will be stored
     * @param  string  $targetColumn  The column name for the ID
     * @return string Generated unique ID
     */
    public function generateWithPrefix(string $prefix, string $targetTable, string $targetColumn): string
    {
        try {
            return DB::transaction(function () use ($prefix, $targetTable, $targetColumn) {

                $today = now()->format('Ymd');
                $baseId = strtoupper($prefix).'-'.$today.'-';

                $lastRecord = DB::table($targetTable)
                    ->where($targetColumn, 'LIKE', $baseId.'%')
                    ->lockForUpdate()
                    ->latest($targetColumn)
                    ->first();

                $serial = 1;
                if ($lastRecord) {
                    $lastSerial = (int) substr($lastRecord->$targetColumn, -4);
                    $serial = $lastSerial + 1;
                }

                $newSerial = str_pad($serial, 4, '0', STR_PAD_LEFT);

                return $baseId.$newSerial;

            }, 3);

        } catch (\Throwable $e) {
            Log::error('Unique ID Generation Failed: '.$e->getMessage());

            return strtoupper($prefix).'-'.time().'-'.rand(1000, 9999);
        }
    }
}
