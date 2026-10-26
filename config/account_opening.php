<?php

/*
|--------------------------------------------------------------------------
| Account Opening (AOF) module settings
|--------------------------------------------------------------------------
|
|   AOF_MINIMUM_INITIAL_DEPOSIT=500   Smallest initial deposit (in the account currency)
|                                      accepted on the Account and Due Diligence steps.
|
|   AOF_MAKER_CHECKER_ENABLED=true    Maker-checker: the user who created or submitted a
|                                      request cannot approve or reject it (super admin included).
|   AOF_ALLOW_SELF_APPROVAL=false     Let one person act as maker and checker (small branches).
|                                      Ignored when maker-checker is disabled.
|   AOF_LOCK_AFTER_APPROVAL=true      An approved request is locked for every user; only a
|                                      super admin can unlock one particular request (with a reason).
|   AOF_STRICT_STEPS=true             Step-wise wizard: a step opens only when the steps before it are
|                                      complete, and Save & Continue moves on only when the step is
|                                      complete. Set false to let staff jump between steps freely.
|   AOF_PAPER_UPLOADS_DISK=local      Disk for the signatures, thumb impressions and documents uploaded on an old
|                                      (paper) form. Keep it private: they are opened only through the request page.
|
*/

return [
    'minimum_initial_deposit' => (float) env('AOF_MINIMUM_INITIAL_DEPOSIT', 500),

    'maker_checker' => [
        'enabled' => (bool) env('AOF_MAKER_CHECKER_ENABLED', true),
        'allow_self_approval' => (bool) env('AOF_ALLOW_SELF_APPROVAL', false),
    ],

    'lock_after_approval' => (bool) env('AOF_LOCK_AFTER_APPROVAL', true),

    'paper_uploads_disk' => env('AOF_PAPER_UPLOADS_DISK', 'local'),

    'strict_steps' => (bool) env('AOF_STRICT_STEPS', true),
];
