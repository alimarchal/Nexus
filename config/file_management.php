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
|
*/

return [
    'maker_checker' => [
        'enabled' => (bool) env('FMS_MAKER_CHECKER_ENABLED', true),
        'allow_self_approval' => (bool) env('FMS_ALLOW_SELF_APPROVAL', false),
    ],

    'lock_after_approval' => (bool) env('FMS_LOCK_AFTER_APPROVAL', true),
];
