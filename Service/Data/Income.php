<?php

namespace Service\Data;

class Income
{
    public function __construct()
    {
    }

    public function getIncome()
    {
        // TODO: convert this to a database call, obviously  :)
        return [
            [
                'name' => 'savings account',
                'opening_balance' => 10000,
                'current_balance' => 10000,
                'monthly_withdrawal' => 100,
                'apr' => 0.025,
                'begin' => [
                    'after' => null,
                    'year' => 2027,
                    'month' => 1,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'other savings account',
                'opening_balance' => 200,
                'current_balance' => 200,
                'monthly_withdrawal' => 50,
                'apr' => 0.01,
                'begin' => [
                    'after' => null,
                    'year' => 2028,
                    'month' => 2,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'third savings account',
                'opening_balance' => 1500,
                'current_balance' => 1500,
                'monthly_withdrawal' => 75,
                'apr' => 0.00,
                'begin' => [
                    'after' => 1,
                    'year' => null,
                    'month' => null,
                ],
                'status' => 'untapped',
            ],
        ];
    }
}
