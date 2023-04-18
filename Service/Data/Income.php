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
