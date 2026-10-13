<?php

/**
 * The old BAJK paper Account Opening Form: the official Terms & Conditions (points 1-52) and the Indemnity and
 * Undertaking text, exactly as printed on the paper form. Put them in the two files below (the Terms one clause per
 * line, the Indemnity as plain text); the form shows and prints whatever they contain.
 */
return [
    'terms' => is_file($terms = resource_path('paper-form/terms.txt'))
        ? array_values(array_filter(array_map('trim', file($terms))))
        : [],

    'indemnity' => is_file($indemnity = resource_path('paper-form/indemnity.txt'))
        ? trim((string) file_get_contents($indemnity))
        : '',
];
