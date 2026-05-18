<?php

use Alimarchal\IdGenerator\IdGenerator;

if (! function_exists('generateUniqueId')) {
    /**
     * Global helper function to generate unique IDs
     *
     * @param  string  $type  The type from id_prefixes table
     * @param  string  $targetTable  The table where ID will be stored
     * @param  string  $targetColumn  The column name for the ID
     * @return string Generated unique ID
     */
    function generateUniqueId(string $type, string $targetTable, string $targetColumn): string
    {
        return app(IdGenerator::class)
            ->generate($type, $targetTable, $targetColumn);
    }
}

if (! function_exists('generateUniqueIdWithPrefix')) {
    /**
     * Global helper function to generate unique IDs with direct prefix
     *
     * @param  string  $prefix  Direct prefix (e.g., 'INV', 'CMP')
     * @param  string  $targetTable  The table where ID will be stored
     * @param  string  $targetColumn  The column name for the ID
     * @return string Generated unique ID
     */
    function generateUniqueIdWithPrefix(string $prefix, string $targetTable, string $targetColumn): string
    {
        return app(IdGenerator::class)
            ->generateWithPrefix($prefix, $targetTable, $targetColumn);
    }
}
