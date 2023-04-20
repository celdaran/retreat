<?php namespace Service\Engine;

use \Service\Data\Expense;
use \Service\Data\Income;
use \Service\Log;

class Engine
{
    private $plan = [];
    private $audit = [];

    private $expense = [];
    private $income = [];

    private $log;

    private $currentPeriod = 1;

    public function __construct()
    {
        $expense = new Expense();
        $income = new Income();

        $this->expense = $expense->getExpense();
        $this->income = $income->getIncome();
        $this->log = new Log();
        $this->log->setLevel('OFF');

        $this->audit = [
            'expense' => [],
            'income' => [],
        ];
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
        for ($this->currentPeriod = 1; $this->currentPeriod <= $months; $this->currentPeriod++) {

            $expense = $this->getExpenseForPeriod($year, $month);
            $this->adjustIncomeForPeriod($year, $month, $expense);

            $this->plan[] = [
                'period' => $this->currentPeriod,
                'year' => $year,
                'month' => $month,
                'expense' => $expense,
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
            printf("%03d,%4d-%02d,%d\n", $p['period'], $p['year'], $p['month'], $p['expense']);
        }

        foreach ($this->income as $i) {
            printf("Income source: %s\n", $i['name']);
            printf("  Current balance: \$%0.2f\n", $i['current_balance']);
            printf("\n");
        }

        foreach ($this->audit['expense'] as $e) {
            printf("%03d,%4d-%02d,%s,%d,%s\n",
                $e['period'], $e['year'], $e['month'],
                $e['name'], $e['amount'], $e['status'],
            );
        }

        foreach ($this->audit['income'] as $i) {
            printf("%03d,%4d-%02d,%s,%0.2f,%0.2f,%0.2f,%s\n", 
                $i['period'], $i['year'], $i['month'], 
                $i['name'], $i['opening_balance'], $i['current_balance'], $i['max_withdrawal'], $i['status']);
        }
    }

    //------------------------------------------------------------------
    // Private functions
    //------------------------------------------------------------------

    /**
     * Get expenses for a given period
     */
    private function getExpenseForPeriod(int $year, int $month): float
    {
        // Activate expenses based on current period
        $i = 0;
        foreach ($this->expense as $expense) {
            // If we hit a planned expense, see if it's time to activate it
            if ($expense['status'] === 'planned') {
                if (($year >= $expense['begin']['year']) && ($month >= $expense['begin']['month'])) {
                    $this->log->debug("Activating expense $i, in year $year month $month, as planned from the start");
                    $this->expense[$i]['status'] = 'active';
                }
            }
            $i++;
        };

        // Now get amounts, drawing from every participating expense
        $total = 0.00;
        $i = 0;
        foreach ($this->expense as $expense) {
            $this->audit['expense'][] = [
                'period' => $this->currentPeriod,
                'year' => $year,
                'month' => $month,
                'name' => $expense['name'],
                'amount' => $expense['amount'],
                'status' => $expense['status'],
            ];
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
                    $this->log->debug("Ending expense $i, in year $year month $month, as planned from the start");
                    $this->expense[$i]['status'] = 'ended';
                }
            }
            $i++;
        } 

        return round($total, 2);
    }

    /**
     * Adjust income sources per period
     * This is two passes:
     * 1) reducing one or more balances per the $expense per period
     * 2) increasing all balances to account for interest earned
     */
    private function adjustIncomeForPeriod(int $year, int $month, float $expense)
    {
        $this->makeWithdrawals($year, $month, $expense);
        $this->earnInterest();
    }

    /**
     * Withdraw money from fund(s) until expense is matched
     */
    private function makeWithdrawals(int $year, int $month, float $expense)
    {
        $total = 0;

        foreach ($this->income as $income) {
            $this->audit['income'][] = [
                'period' => $this->currentPeriod,
                'year' => $year,
                'month' => $month,
                'name' => $income['name'],
                'opening_balance' => $income['opening_balance'],
                'current_balance' => $income['current_balance'],
                'max_withdrawal' => $income['max_withdrawal'],
                'status' => $income['status'],
            ];
        }

        for ($i = 0; $i < count($this->income); $i++) {

            $this->activateSources($year, $month);

            if ($this->income[$i]['status'] === 'active') {

                // Set withdrawal amount
                $amount = round(min(
                    $expense,                               // When the full expense can be pulled from the source
                    $this->income[$i]['max_withdrawal'],    // If there's a max, cap this period's withdrawal
                    $this->income[$i]['current_balance'],   // Sometimes the current balance is the most we can pull
                    ($expense - $total),                    // And sometimes it's just the small amount we need
                ), 2);

                if ($amount === 0.00) {
                    $this->log->debug("Got a withdrawal amount near zero while pulling from source $i");
                    $this->log->debug("  Amount          = $amount");
                    $this->log->debug("  Target Expense  = $expense");
                    $this->log->debug("  Max Withdrawal  = " . $this->income[$i]['max_withdrawal']);
                    $this->log->debug("  Current Balance = " . $this->income[$i]['current_balance']);
                    $this->log->debug("  Top-Off amount  = " . ($expense - $total));
                    exit;
                }
                $this->log->debug("Pulling $amount to meet $expense from income source $i in $year-$month");
                $total = round($total + $amount, 2);

                // Reduce balance by withdrawal amount
                $this->income[$i]['current_balance'] -= $amount;

                // If we just depleted this source, activate the next one(s)
                if ($this->income[$i]['current_balance'] <= 0) {
                    $this->income[$i]['current_balance'] = 0;
                    $this->income[$i]['status'] = 'depleted';
                }

                $this->log->debug("Current balance of income $i is " . $this->income[$i]['current_balance']);

                if ($total === $expense) {
                    // Just hack our way out of this
                    break;
                }
            }
        }

        if ($total < $expense) {
            $this->log->warn("Could not find enough money $total in $year-$month to cover expense $expense");
        }

        return;
    }

    /**
     * Activate income sources per plan
     */
    private function activateSources(int $year, int $month)
    {
        // Activate withdrawals based on current period
        for ($i = 0; $i < count($this->income); $i++) {
            // If we hit an untapped income, see if it's time to tap it
            if ($this->income[$i]['status'] === 'untapped') {
                // Two ways to tap!
                // 1. It can follow a previously-depleted source
                if ($this->income[$i]['begin']['after'] !== null) {
                    if ($this->income[$this->income[$i]['begin']['after']]['status'] === 'depleted') {
                        $this->log->debug("Activating income source $i, in year $year month $month, after previous source depleted");
                        $this->income[$i]['status'] = 'active';
                    }
                }
                // 2. It can begin during a specified year and month
                if ($this->income[$i]['begin']['after'] === null) {
                    if (($year >= $this->income[$i]['begin']['year']) && ($month >= $this->income[$i]['begin']['month'])) {
                        $this->log->debug("Activating income source $i, in year $year month $month, as planned from the start");
                        $this->income[$i]['status'] = 'active';
                    }
                }
            }
        }
    }

    /**
     * Loop through each income source and add interest
     */
    private function earnInterest()
    {
        for ($i = 0; $i < count($this->income); $i++) {
            if ($this->income[$i]['current_balance'] > 0) {
                $this->income[$i]['current_balance'] += $this->calculateInterest(
                    $this->income[$i]['current_balance'],
                    $this->income[$i]['apr']
                );
            }
        }
    }

    /**
     * Calculate compound interest
     */
    private function calculateInterest(float $p, float $r): float
    {
        // Convert rate
        $r = $r / 100;

        // Set time
        $t = 1 / 12;

        // Calculate new value
        $v = $p * (1 + $r / 12) ** (12 * $t);

        // Return *just* the interest
        return round($v - $p, 2);
    }

}
