<?php

use App\Models\AccountOpeningPaperForm;
use App\Models\AccountOpeningRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every old (paper form) request was saved as "Individual/Joint/Sole", even a business account. Set the form type of
     * each old request from the Nature of Account on its paper form. Only the form type is touched, and a request whose
     * paper form has no Nature of Account yet keeps the type it has.
     */
    public function up(): void
    {
        AccountOpeningPaperForm::query()
            ->orderBy('id')
            ->chunk(200, function ($forms): void {
                foreach ($forms as $form) {
                    $nature = $form->data['account_nature'] ?? null;

                    if (! in_array($nature, ['individual', 'business'], true)) {
                        continue;
                    }

                    DB::table('account_opening_requests')
                        ->where('id', $form->account_opening_request_id)
                        ->where('form_edition', AccountOpeningRequest::EDITION_OLD)
                        ->update(['aof_form_type' => AccountOpeningRequest::formTypeForNature($nature)]);
                }
            });
    }

    public function down(): void
    {
        // The earlier (wrong) form type cannot be told apart from a correct one, so nothing is restored.
    }
};
