<?php

namespace App\Services;

use Carbon\CarbonImmutable;

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
        $principalOutstanding = $this->toScaledString($principalAmount);
        $monthlyInstallment = bcdiv($principalAmount, (string) $tenure, 6);
        $totalInterestRate = bcadd($kiborRate, $spreadRate, 6);
        $periodStart = CarbonImmutable::parse($disbursementDate)->startOfDay();
        $rows = [];

        for ($installmentNumber = 1; $installmentNumber <= $tenure && bccomp($principalOutstanding, '0', 6) === 1; $installmentNumber++) {
            $periodEnd = $periodStart->endOfMonth()->startOfDay();
            $dueDate = $periodEnd->addDay();
            $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
            $daysInYear = $periodStart->isLeapYear() ? 366 : 365;
            $product = bcdiv(bcmul($principalOutstanding, $totalInterestRate, 10), '100', 6);
            $interestPerDay = bcdiv($product, (string) $daysInYear, 6);
            $monthlyInterest = bcmul($interestPerDay, (string) $daysInPeriod, 6);
            $installment = $installmentNumber === $tenure || bccomp($monthlyInstallment, $principalOutstanding, 6) === 1
                ? $principalOutstanding
                : $monthlyInstallment;
            $totalInstallment = bcadd($installment, $monthlyInterest, 6);
            $balanceAfterInstallment = bcsub($principalOutstanding, $installment, 6);

            $rows[] = [
                'installment_no' => $installmentNumber,
                'period_start_date' => $periodStart->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'days' => $daysInPeriod,
                'principal_amount_os' => $principalOutstanding,
                'installment_per_month' => $installment,
                'product' => $product,
                'interest_rate_per_month' => $interestPerDay,
                'total_interest' => $monthlyInterest,
                'total_rate' => $totalInterestRate,
                'total_installment' => $totalInstallment,
                'principal_balance_after_installment' => $balanceAfterInstallment,
            ];

            $principalOutstanding = $balanceAfterInstallment;
            $periodStart = $dueDate;
        }

        return $rows;
    }

    private function toScaledString(string $value): string
    {
        return bcadd($value, '0', 6);
    }
}
