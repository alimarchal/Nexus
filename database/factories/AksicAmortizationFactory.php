<?php

namespace Database\Factories;

use App\Models\Aksic;
use App\Models\AksicAmortization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AksicAmortization>
 */
class AksicAmortizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aksic_id' => Aksic::factory(),
            'installment_no' => 1,
            'period_start_date' => '2026-05-11',
            'due_date' => '2026-06-01',
            'days' => 21,
            'principal_amount_os' => 1000000,
            'installment_per_month' => 16666.666667,
            'product' => 140400,
            'interest_rate_per_month' => 384.657534,
            'total_interest' => 8077.808214,
            'total_rate' => 14.04,
            'total_installment' => 24744.474881,
            'principal_balance_after_installment' => 983333.333333,
        ];
    }
}
