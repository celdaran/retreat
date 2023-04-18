<?php

namespace Service\Data;

class Expense
{
    public function __construct()
    {
    }

    public function getExpense()
    {
        // TODO: convert this to a database call, obviously  :)
        return [
            [
                'name' => 'mortgage',
                'amount' => 1500,
                'begin' => [
                    'year' => 2027,
                    'month' => 1,
                ],
                'end' => [
                    'year' => 2030,
                    'month' => 3,
                ],
                'status' => 'planned',
            ],

            [
                'name' => 'groceries',
                'amount' => 200,
                'begin' => [
                    'year' => 2027,
                    'month' => 7,
                ],
                'end' => [
                    'year' => 2030,
                    'month' => 7,
                ],
                'status' => 'planned',
            ],
        ];
    }
}
