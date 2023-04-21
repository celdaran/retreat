<?php

namespace Service\Data;

use Service\Data\Database;

class Expense
{
    private $data;

    public function __construct()
    {
        $this->data = new Database();
    }

    public function getExpense(string $scenario = 'base')
    {
        // $expense = $this->data->select("SELECT * FROM expense");
        // maybe do stuff to $expense (not "maybe" because we now have inheritance)
        // return $expense;

        $expenses = [];

        $expenses['base'] = [
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

        $expenses['alt'] = [
            'description' => 'cheaper cell phone plan',
            'parent' => 'base',
            'scenario' => [
                [
                    'name' => 'cell phone',
                    'amount' => 5.00,
                ],
            ],
        ];

        if ($expenses[$scenario]['parent'] === null) {
            return $expenses[$scenario]['scenario'];
        } else {
            $parentScenarioName = $expenses[$scenario]['parent'];
            $parentScenario = $expenses[$parentScenarioName]['scenario'];
            $childScenario = $expenses[$scenario]['scenario'];

            // no, start with nothing
            $mergedScenario = [];
            foreach ($parentScenario as $scenario) {
                $mergedScenario[] = $this->merge($scenario, $childScenario);
            }

            // then loop through the child and overwrite keys
            /*
            foreach ($childScenario as $scenario) {
                foreach ($scenario as $k => $v) {
                    $this->merge($)
                }
            }
            */

            return $mergedScenario;
        }
    }

    private function merge(array $parentScenario, array $children)
    {
        $merge = [];

        foreach ($children as $childScenario) {
            if ($childScenario['name'] === $parentScenario['name']) {
                return array_merge($parentScenario, $childScenario);
            }
        }

        return $parentScenario;
    }
}
