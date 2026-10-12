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
|
*/

return [
    'minimum_initial_deposit' => (float) env('AOF_MINIMUM_INITIAL_DEPOSIT', 500),

    'maker_checker' => [
        'enabled' => (bool) env('AOF_MAKER_CHECKER_ENABLED', true),
        'allow_self_approval' => (bool) env('AOF_ALLOW_SELF_APPROVAL', false),
    ],

    'lock_after_approval' => (bool) env('AOF_LOCK_AFTER_APPROVAL', true),
];
