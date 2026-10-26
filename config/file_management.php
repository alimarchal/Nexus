<?php

/*
|--------------------------------------------------------------------------
| File Management maker-checker settings
|--------------------------------------------------------------------------
|
|   FMS_MAKER_CHECKER_ENABLED=true    A record is made by one user and approved by another before it is final.
|                                      When false, records are editable at any time and need no approval.
|   FMS_ALLOW_SELF_APPROVAL=false     Let one person act as maker and checker (small offices).
|                                      Ignored when maker-checker is disabled.
|   FMS_LOCK_AFTER_APPROVAL=true      An approved record is locked for every user; only a super admin can
|                                      unlock one particular record (with a reason).
|   FMS_PAGES_DISK=local               Disk that holds the scanned pages. Keep it private ("local"): a page must only be
|                                      opened through the protected page route, which checks access and logs the view.
|
*/

return [
    'maker_checker' => [
        'enabled' => (bool) env('FMS_MAKER_CHECKER_ENABLED', true),
        'allow_self_approval' => (bool) env('FMS_ALLOW_SELF_APPROVAL', false),
    ],

    'pages_disk' => env('FMS_PAGES_DISK', 'local'),

    'lock_after_approval' => (bool) env('FMS_LOCK_AFTER_APPROVAL', true),
];
