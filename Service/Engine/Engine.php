<?php namespace Service\Engine;

use \Service\Data\Income;
use \Service\Data\Expense;

class Engine
{
    private $plan = [];

    private $income = [];
    private $expense = [];

    private $max_withdrawal = 5000;

    public function __construct()
    {
        $income = new Income();
        $expense = new Expense();

        $this->income = $income->getIncome();
        $this->expense = $expense->getExpense();
    }

    /**
     * Core function of the engine: to take all inputs and generate a plan
     */
    public function run(int $startYear, int $startMonth, int $months)
    {
        // Track year and month
        $year = $startYear;
        $month = $startMonth;

        // Loop until the requested number of months have passed.
        // What will fundamentally happen here is that for each and every month,
        // there will be income and expenses. That's it. The job of the engine,
        // then, is to pull income from n-number of sources, figure out how much
        // to spend each month. The source(s) will have their balances reduced
        // each month based on expenses. The source(s) will have their balances
        // increased each month based on investment gains.

        for ($i = 1; $i <= $months; $i++) {

            $income = $this->getIncomeForPeriod($year, $month);
            $expense = $this->getExpenseForPeriod($year, $month);

            // Todo: reinvest monthly surplus into an account somewhere?
            $this->plan[] = [
                'period' => $i,
                'year' => $year,
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'surplus' => $income - $expense,
            ];

            if ($month % 12 === 0) {
                $year++;
                $month = 0;
            }

            $month++;
        }

    }

    /**
     * Core function of the engine: to take the plan and render output
     */
    public function render()
    {
        foreach ($this->plan as $p) {
            printf("%03d,%4d-%02d,+%d,-%d,%d\n", $p['period'], $p['year'], $p['month'], $p['income'], $p['expense'], $p['surplus']);
        }

        foreach ($this->income as $i) {
            printf("Income source: %s\n", $i['name']);
            printf("  Current balance: \$%0.2f\n", $i['current_balance']);
            printf("\n");
        }
    }

    //------------------------------------------------------------------
    // Private functions
    //------------------------------------------------------------------

    private function getIncomeForPeriod(int $year, int $month)
    {
        // Activate withdrawals based on current period
        $i = 0;
        foreach ($this->income as $income) {
            // First, see if there's enough money left
            if ($income['current_balance'] < $income['monthly_withdrawal']) {
                $this->income[$i]['status'] = 'depleted';
            }
            // If we hit an untapped income, see if it's time to tap it
            if ($income['status'] === 'untapped') {
                // Two ways to tap!
                // 1. It can follow a previously-depleted source
                if ($income['begin']['after'] !== null) {
                    if ($this->income[$income['begin']['after']]['status'] === 'depleted') {
                        print("***DEBUG: Activating income source $i, in year $year month $month, after previous source depleted\n");
                        $this->income[$i]['status'] = 'active';
                    }
                }
                // 2. It can begin during a specified year and month
                if ($income['begin']['after'] === null) {
                    if (($year >= $income['begin']['year']) && ($month >= $income['begin']['month'])) {
                        print("***DEBUG: Activating income source $i, in year $year month $month, as planned from the start\n");
                        $this->income[$i]['status'] = 'active';
                    }
                }
            }
            $i++;
        };

        // Now get amounts, drawing from every eligible income source
        $total = 0;
        $i = 0;
        foreach ($this->income as $income) {
            // Check monthly withdrawl cap
            if ($total <= $this->max_withdrawal) {

                // Source must be active
                if ($income['status'] === 'active') {

                    // Check balance
                    if ($income['current_balance'] >= $income['monthly_withdrawal']) {
                        // Withdraw specified amount
                        $total += $income['monthly_withdrawal'];
                        // Reduce balance by withdrawal amount
                        $this->income[$i]['current_balance'] -= $income['monthly_withdrawal'];
                        // Increase balance by interest rate
                        $this->income[$i]['current_balance'] += $this->calculateInterest(
                            $this->income[$i]['current_balance'],
                            $this->income[$i]['apr'],
                            12,
                            1/12,
                        );
                    } else {
                        $this->income[$i]['status'] = 'depleted';
                    }
                }
            }
            $i++;
        }

        return $total;
    }

    private function calculateInterest(float $p, float $k, int $m, float $n)
    {
        // P*(1+(k/m))^(m*n)
        return ($p * (1 + ($k / $m)) ** ($m * $n)) - $p;
    }

    /**
     * Get expenses for a given period
     */
    private function getExpenseForPeriod(int $year, int $month)
    {
        // Activate expenses based on current period
        $i = 0;
        foreach ($this->expense as $expense) {
            // If we hit a planned expense, see if it's time to activate it
            if ($expense['status'] === 'planned') {
                if (($year >= $expense['begin']['year']) && ($month >= $expense['begin']['month'])) {
                    print("***DEBUG: Activating expense $i, in year $year month $month, as planned from the start\n");
                    $this->expense[$i]['status'] = 'active';
                }
            }
            $i++;
        };

        // Now get amounts, drawing from every participating expense
        $total = 0;
        $i = 0;
        foreach ($this->expense as $expense) {
            if ($expense['status'] === 'active') {
                $total += $expense['amount'];
            }
            $i++;
        } 

        // Lastly, has it ended?
        $i = 0;
        foreach ($this->expense as $expense) {
            if ($expense['status'] === 'active') {
                if (($year >= $expense['end']['year']) && ($month >= $expense['end']['month'])) {
                    print("***DEBUG: Ending expense $i, in year $year month $month, as planned from the start\n");
                    $this->expense[$i]['status'] = 'ended';
                }
            }
            $i++;
        } 

        return $total;
    }


}
