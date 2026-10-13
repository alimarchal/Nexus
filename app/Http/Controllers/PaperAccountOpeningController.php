<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AccountOpeningWorkflowException;
use App\Http\Requests\UpdatePaperAccountOpeningRequest;
use App\Models\AccountOpeningRequest;
use App\Services\AccountOpeningWorkflow;
use App\Support\PaperForm\PaperFormSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Fill in and save the old BAJK paper Account Opening Form of an "old" account opening request.
 * Viewing, printing and the maker-checker actions are shared with AccountOpeningController.
 */
class PaperAccountOpeningController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AccountOpeningWorkflow $workflow) {}

    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:edit account openings', only: ['edit', 'update']),
        ];
    }

    public function edit(AccountOpeningRequest $accountOpeningRequest): View|RedirectResponse
    {
        $this->authorizeRequest($accountOpeningRequest);

        if ($blocked = $this->blockedFromEditing($accountOpeningRequest)) {
            return $blocked;
        }

        $accountOpeningRequest->load('paperForm');

        return view('account-openings.paper.edit', [
            'accountOpeningRequest' => $accountOpeningRequest,
            'values' => old('data', ($accountOpeningRequest->paperForm?->values() ?? []) + [
                'branch_code' => $accountOpeningRequest->branch?->code,
                'branch_name' => $accountOpeningRequest->branch?->name,
                'form_date' => $accountOpeningRequest->request_date?->toDateString(),
            ]),
        ]);
    }

    public function update(UpdatePaperAccountOpeningRequest $request, AccountOpeningRequest $accountOpeningRequest): RedirectResponse
    {
        $this->authorizeRequest($accountOpeningRequest);

        if ($blocked = $this->blockedFromEditing($accountOpeningRequest)) {
            return $blocked;
        }

        $clean = PaperFormSchema::clean((array) $request->input('data', []));
        $keyWord = $clean['key_word'] ?? null;
        unset($clean['key_word']);

        DB::transaction(function () use ($request, $accountOpeningRequest, $clean, $keyWord): void {
            $form = $accountOpeningRequest->paperForm()->firstOrNew();
            $before = $form->values();

            $form->fill([
                'data' => $clean,
                'key_word' => $keyWord,
                'display_name' => PaperFormSchema::displayName($clean),
            ]);
            $accountOpeningRequest->paperForm()->save($form);

            $changes = $this->changes($before, $form->values());

            if ($changes !== []) {
                $this->workflow->record($accountOpeningRequest, 'step_saved', $request->user(), 'paper', $changes);
            }
        });

        return redirect()
            ->route('account-openings.show', $accountOpeningRequest)
            ->with('success', 'The old account opening form was saved.');
    }

    /**
     * What changed, by field label. The Key/Secret Word is never written to the history.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{old: string|null, new: string|null}>
     */
    private function changes(array $before, array $after): array
    {
        $labels = PaperFormSchema::labels();
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $name) {
            $old = $before[$name] ?? null;
            $new = $after[$name] ?? null;

            if ($old == $new) {
                continue;
            }

            $mask = fn (mixed $value): ?string => $value === null || $value === '' || $value === [] ? null : '••••••';
            $text = fn (mixed $value): ?string => $value === null || $value === '' || $value === [] ? null : (is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE));

            $changes[$labels[$name] ?? $name] = $name === 'key_word'
                ? ['old' => $mask($old), 'new' => $mask($new)]
                : ['old' => $text($old), 'new' => $text($new)];
        }

        return $changes;
    }

    private function authorizeRequest(AccountOpeningRequest $accountOpeningRequest): void
    {
        abort_unless(
            AccountOpeningRequest::visibleTo(auth()->user())->whereKey($accountOpeningRequest->id)->exists(),
            404
        );
        abort_unless($accountOpeningRequest->isOldEdition(), 404);
    }

    private function blockedFromEditing(AccountOpeningRequest $accountOpeningRequest): ?RedirectResponse
    {
        try {
            $this->workflow->assertEditable($accountOpeningRequest);
        } catch (AccountOpeningWorkflowException $exception) {
            return redirect()->route('account-openings.show', $accountOpeningRequest)->with('error', $exception->getMessage());
        }

        return null;
    }
}
