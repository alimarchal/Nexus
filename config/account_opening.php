<?php

/*
|--------------------------------------------------------------------------
| Account Opening (AOF) module settings
|--------------------------------------------------------------------------
|
|   AOF_MINIMUM_INITIAL_DEPOSIT=500   Smallest initial deposit (in the account currency)
|                                      accepted on the Account and Due Diligence steps.
|
*/

return [
    'minimum_initial_deposit' => (float) env('AOF_MINIMUM_INITIAL_DEPOSIT', 500),
];
