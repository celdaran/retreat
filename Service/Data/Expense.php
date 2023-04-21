<?php namespace Service\Data;

use Service\Data\Database;

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
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
                ],

                [
                    'name' => 'groceries',
                    'amount' => 200.00,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
                ],

                [
                    'name' => 'cell phone',
                    'amount' => 125.00,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'end_year' => 2999,
                    'end_month' => 12,
                    'status' => 'planned',
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
