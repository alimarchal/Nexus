<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * AKSIC (PM Youth Loan Scheme) repayment schedule.
 *
 * Portal Change Requirements (demo 22-Sep-2026):
 *   #3  First instalment -- principal outstanding stays equal to the disbursed
 *       amount. Instalment 1 covers the broken period from the disbursement date
 *       to the first month-end and charges markup only (actual days / 365 or 366).
 *       Principal repayment starts from instalment 2.
 *   #10 Markup must match the scheme guideline. From instalment 2 onward each
 *       instalment charges one month of markup on the opening principal
 *       (principal x rate / 12) and repays principal in equal parts, so the
 *       markup of the regular instalments is
 *           P x rate / 12 x (tenure + 1) / 2
 *       e.g. Rs 10 lakh, 60 months, 14.04%  ->  Rs 356,850 (guideline figure);
 *            Rs 15 lakh -> 535,275; Rs 20 lakh -> 713,700.
 *
 * A schedule therefore has (tenure + 1) rows: one markup-only broken-period row
 * followed by `tenure` regular monthly instalments, all due on month-ends.
 */
class AksicAmortizationScheduleGenerator
{
    /**
     * @return array<int, array<string, int|string>>
     */
    public function generate(
        string $principalAmount,
        int $tenure,
        string $disbursementDate,
        string $kiborRate,
        string $spreadRate,
    ): array {
        $principal = bcadd($principalAmount, '0', 6);
        $totalRate = bcadd($kiborRate, $spreadRate, 6);
        $rows = [];

        // ---- Instalment 1: broken period, markup only (#3) -------------------
        $periodStart = CarbonImmutable::parse($disbursementDate)->startOfDay();
        $periodEnd = $periodStart->endOfMonth()->startOfDay();
        $days = (int) $periodStart->diffInDays($periodEnd) + 1;
        $daysInYear = $periodStart->isLeapYear() ? 366 : 365;
        $markupPerYear = $this->markupPerYear($principal, $totalRate);
        $brokenMarkup = bcdiv(bcmul($markupPerYear, (string) $days, 10), (string) $daysInYear, 6);

        $rows[] = [
            'installment_no' => 1,
            'period_start_date' => $periodStart->toDateString(),
            'due_date' => $periodEnd->toDateString(),
            'days' => $days,
            'principal_amount_os' => $principal,
            'installment_per_month' => '0.000000',
            'product' => $markupPerYear,
            'interest_rate_per_month' => bcdiv($markupPerYear, '12', 6),
            'total_interest' => $brokenMarkup,
            'total_rate' => $totalRate,
            'total_installment' => $brokenMarkup,
            'principal_balance_after_installment' => $principal,
        ];

        // ---- Instalments 2..tenure+1: regular monthly (#10) ------------------
        $monthlyPrincipal = bcdiv($principal, (string) $tenure, 6);
        $outstanding = $principal;
        $periodStart = $periodEnd->addDay();

        for ($month = 1; $month <= $tenure && bccomp($outstanding, '0', 6) === 1; $month++) {
            $periodEnd = $periodStart->endOfMonth()->startOfDay();
            $markupPerYear = $this->markupPerYear($outstanding, $totalRate);
            $markupPerMonth = bcdiv($markupPerYear, '12', 6);
            $principalPart = $month === $tenure || bccomp($monthlyPrincipal, $outstanding, 6) === 1
                ? $outstanding
                : $monthlyPrincipal;
            $closing = bcsub($outstanding, $principalPart, 6);

            $rows[] = [
                'installment_no' => $month + 1,
                'period_start_date' => $periodStart->toDateString(),
                'due_date' => $periodEnd->toDateString(),
                'days' => (int) $periodStart->diffInDays($periodEnd) + 1,
                'principal_amount_os' => $outstanding,
                'installment_per_month' => $principalPart,
                'product' => $markupPerYear,
                'interest_rate_per_month' => $markupPerMonth,
                'total_interest' => $markupPerMonth,
                'total_rate' => $totalRate,
                'total_installment' => bcadd($principalPart, $markupPerMonth, 6),
                'principal_balance_after_installment' => $closing,
            ];

            $outstanding = $closing;
            $periodStart = $periodEnd->addDay();
        }

        return $rows;
    }

    /**
     * Guideline markup for the regular instalments: P x rate / 12 x (n + 1) / 2.
     */
    public function guidelineMarkup(string $principalAmount, int $tenure, string $totalRate): string
    {
        return bcdiv(
            bcmul(bcmul($principalAmount, $totalRate, 10), (string) ($tenure + 1), 10),
            '2400',
            6,
        );
    }

    private function markupPerYear(string $amount, string $totalRate): string
    {
        return bcdiv(bcmul($amount, $totalRate, 10), '100', 6);
    }
}
