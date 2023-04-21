<?php namespace Service\Data;

use Service\Data\Database;

class Income extends Account
{
    public function getIncome(string $scenarioName)
    {
        return $this->getScenario($scenarioName);
    }

    public function getScenarios()
    {
        $scenarios = [];

        $scenarios['base'] = [
            'description' => 'Base scenario',
            'parent' => null,
            'scenario' => [
                [
                    'name' => 'savings account',
                    'opening_balance' => 1000.00,
                    'current_balance' => 1000.00,
                    'max_withdrawal' => 510.00,
                    'apr' => 2.500,
                    'begin_after' => null,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'status' => 'untapped',
                ],

                [
                    'name' => 'other savings account',
                    'opening_balance' => 200.00,
                    'current_balance' => 200.00,
                    'max_withdrawal' => 50.00,
                    'apr' => 1.000,
                    'begin_after' => 0,
                    'begin_year' => null,
                    'begin_month' => null,
                    'status' => 'untapped',
                ],

                [
                    'name' => 'third savings account',
                    'opening_balance' => 1500.00,
                    'current_balance' => 1500.00,
                    'max_withdrawal' => 75.00,
                    'apr' => 0.000,
                    'begin_after' => 1,
                    'begin_year' => null,
                    'begin_month' => null,
                    'status' => 'untapped',
                ],

                [
                    'name' => 'social security',
                    'opening_balance' => 250000.00,
                    'current_balance' => 250000.00,
                    'max_withdrawal' => 5000.00,
                    'apr' => 2.100,
                    'begin_after' => null,
                    'begin_year' => 2033,
                    'begin_month' => 2,
                    'status' => 'untapped',
                ],

                [
                    'name' => 'rsu1',
                    'opening_balance' => 1000000.00,
                    'current_balance' => 1000000.00,
                    'max_withdrawal' => 1000000.00,
                    'apr' => 3.015,
                    'begin_after' => null,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'status' => 'untapped',
                ],

                [
                    'name' => 'rsu2',
                    'opening_balance' => 500000.00,
                    'current_balance' => 500000.00,
                    'max_withdrawal' => 500000.00,
                    'apr' => 3.015,
                    'begin_after' => null,
                    'begin_year' => 2026,
                    'begin_month' => 1,
                    'status' => 'untapped',
                ],
            ],
        ];

        $scenarios['alt'] = [
            'description' => 'Same as base, but asset rsu2 begins in March instead',
            'parent' => 'base',
            'scenario' => [
                [
                    'name' => 'other savings account',
                    'max_withdrawal' => 75.00,
                    'apr' => 1.500,
                ],
                [
                    'name' => 'rsu2',
                    'begin_month' => 3,
                ]
            ]
        ];

        return $scenarios;
    }

}
