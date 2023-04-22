<?php namespace App\Service\Data;

use App\Service\Data\Database;

class Expense extends Account
{
    public function getExpense(string $scenarioName)
    {
        return $this->getScenario($scenarioName);
    }

    public function getScenarios()
    {
        $scenarios = [];

        $scenarios['base'] = [
            'description' => 'base set of expenses',
            'parent' => null,
            'scenario' => [
                [
                    'name' => 'mortgage',
                    'amount' => 1500.00,
                    'inflation_rate' => 0.000,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
                    'repeat_every' => null,
                ],

                [
                    'name' => 'groceries',
                    'amount' => 200.00,
                    'inflation_rate' => 5.000,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
                    'repeat_every' => null,
                ],

                [
                    'name' => 'cell phone',
                    'amount' => 125.00,
                    'inflation_rate' => -2.000,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
                    'repeat_every' => null,
                ],

                [
                    'name' => 'sales taxes',
                    'amount' => 12000.00,
                    'inflation_rate' => 1.000,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2026,
                    'end_month' => 1,
                    'status' => 'planned',
                    'repeat_every' => 3,
                ],
            ],
        ];

        $scenarios['alt'] = [
            'description' => 'cheaper cell phone plan',
            'parent' => 'base',
            'scenario' => [
                [
                    'name' => 'cell phone',
                    'amount' => 5.00,
                ],
            ],
        ];

        return $scenarios;
    }

}
