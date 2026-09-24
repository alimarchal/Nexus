<?php

/*
|--------------------------------------------------------------------------
| AKSIC module switches
|--------------------------------------------------------------------------
|
| Each option is OFF unless it is turned on in .env, so a server without
| these lines shows no Excel import, no Excel export and no demo data.
|
|   AKSIC_EXCEL_IMPORT=true   Import menu: Excel template + "Import from Excel"
|   AKSIC_EXCEL_EXPORT=true   "Export to Excel (CSV)" in the Export / Print menu
|   AKSIC_DEMO_DATA=true      "Replace / remove demo cases" (still local + super admin only)
|
*/

return [
    'excel_import' => (bool) env('AKSIC_EXCEL_IMPORT', false),
    'excel_export' => (bool) env('AKSIC_EXCEL_EXPORT', false),
    'demo_data' => (bool) env('AKSIC_DEMO_DATA', false),
];
