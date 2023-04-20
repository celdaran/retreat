<?php namespace Service\Data;

use \Service\Data\Database;

class Expense
{
    private $data;

    public function __construct()
    {
        $this->data = new Database();
    }

    public function getExpense()
    {
        // $expense = $this->data->select("SELECT * FROM expense");
        // maybe do stuff to $expense
        // return $expense;

        return [
            [
                'name' => 'mortgage',
                'amount' => 1500.00,
                'begin' => [
                    'year' => 2026,
                    'month' => 1,
                ],
                'end' => [
                    'year' => 2999,
                    'month' => 12,
                ],
                'status' => 'planned',
            ],

            [
                'name' => 'groceries',
                'amount' => 200.00,
                'begin' => [
                    'year' => 2026,
                    'month' => 1,
                ],
                'end' => [
                    'year' => 2999,
                    'month' => 12,
                ],
                'status' => 'planned',
            ],

            [
                'name' => 'cell phone',
                'amount' => 125.00,
                'begin' => [
                    'year' => 2026,
                    'month' => 1,
                ],
                'end' => [
                    'year' => 2999,
                    'month' => 12,
                ],
                'status' => 'planned',
            ],
        ];
    }
}
