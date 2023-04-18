<?php

namespace Service\Data;
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
