<?php namespace Service\Data;

use \Service\Data\Database;

class Income
{
    private $data;

    public function __construct()
    {
        $this->data = new Database();
    }

    public function getIncome()
    {
        // $income = $this->data->select("SELECT * FROM income");
        // maybe do stuff to $income
        // return $income;

        return [
            [
                'name' => 'savings account',
                'opening_balance' => 1000.00,
                'current_balance' => 1000.00,
                'max_withdrawal' => 510.00,
                'apr' => 2.500,
                'begin' => [
                    'after' => null,
                    'year' => 2026,
                    'month' => 1,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'other savings account',
                'opening_balance' => 200.00,
                'current_balance' => 200.00,
                'max_withdrawal' => 50.00,
                'apr' => 1.000,
                'begin' => [
                    'after' => 0,
                    'year' => null,
                    'month' => null,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'third savings account',
                'opening_balance' => 1500.00,
                'current_balance' => 1500.00,
                'max_withdrawal' => 75.00,
                'apr' => 0.000,
                'begin' => [
                    'after' => 1,
                    'year' => null,
                    'month' => null,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'social security',
                'opening_balance' => 250000.00,
                'current_balance' => 250000.00,
                'max_withdrawal' => 5000.00,
                'apr' => 2.100,
                'begin' => [
                    'after' => null,
                    'year' => 2033,
                    'month' => 2,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'rsu1',
                'opening_balance' => 1000000.00,
                'current_balance' => 1000000.00,
                'max_withdrawal' => 1000000.00,
                'apr' => 3.015,
                'begin' => [
                    'after' => null,
                    'year' => 2026,
                    'month' => 1,
                ],
                'status' => 'untapped',
            ],

            [
                'name' => 'rsu2',
                'opening_balance' => 500000.00,
                'current_balance' => 500000.00,
                'max_withdrawal' => 500000.00,
                'apr' => 3.015,
                'begin' => [
                    'after' => null,
                    'year' => 2026,
                    'month' => 1,
                ],
                'status' => 'untapped',
            ],
        ];
    }
}
