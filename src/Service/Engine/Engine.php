<?php namespace App\Service\Engine;

use App\Service\Data\Expense;
use App\Service\Data\Asset;
use App\Service\Log;
use App\Service\Engine\Util;

class Engine
{
    private $plan = [];
    private $audit = [];

    private $expense = [];
    private $asset = [];

    private $log;
    private $util;
    private $fmt;

    private $currentPeriod = 1;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
    /**
     * Constructor
     * Optionally pass in asset and/or expense scenario names and this
     * method preps the engine for running a simulation and rendering
     * the results.
     */
    public function __construct(string $expenseScenario = 'base', string $assetScenario = null)
    {
        $expense = new Expense();
        $asset = new Asset();

        if ($assetScenario === null) {
            $assetScenario = $expenseScenario;
        }
        $this->expense = $expense->getScenario($expenseScenario);
        $this->asset = $asset->getScenario($assetScenario);

        $this->log = new Log();
        $this->log->setLevel($_ENV['LOG_LEVEL']);
        $this->util = new Util();
        $this->fmt = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);

        $this->audit = [
            'expense' => [],
            'asset' => [],
        ];
    }

    /**
     * Core function of the engine: to take all inputs and generate a plan
     */
    public function run(int $startYear, int $startMonth, int $months): bool
    {
        // Track year and month
        $year = $startYear;
        $month = $startMonth;

        // Loop until the requested number of months have passed.
        for ($this->currentPeriod = 1; $this->currentPeriod <= $months; $this->currentPeriod++) {

            $expense = $this->getExpenseForPeriod($year, $month);
            if ($this->adjustAssetForPeriod($year, $month, $expense)) {

                $assets = [];
                foreach ($this->asset as $asset) {
                    $assets[$asset['name']] = $asset['current_balance'];
                }

                $planEntry = [
                    'period' => $this->currentPeriod,
                    'year' => $year,
                    'month' => $month,
                    'expense' => $expense,
                    'assets' => $assets,
                ];

                $this->plan[] = $planEntry;

                if ($month % 12 === 0) {
                    $year++;
                    $month = 0;
                }

                $month++;
            } else {
                return false;
            }

        }

        return true;
    }

    public function assets(): array
    {
        return $this->asset;
    }

    public function render($format = 'csv')
    {
        printf("%s,%s,%s,", 'period', 'month', 'expense');
        $i = 0;
        foreach ($this->plan as $p) {
            if ($i === 0) {
                if (count($p['assets']) > 0) {
                    foreach (array_keys($p['assets']) as $assetName) {
                        printf("\"%s\",", addslashes($assetName));
                    }
                }
                print("\n");
            }
            printf("%03d,%4d-%02d,%.2f,", $p['period'], $p['year'], $p['month'], $p['expense']);
            foreach ($p['assets'] as $asset) {
                printf("%.2f,", $asset);
            }
            print("\n");
            $i++;
        }
    }

    public function report()
    {
        foreach ($this->asset as $i) {
            printf("Asset: %s\n", $i['name']);
            printf("  Current balance: %s\n", $this->fmt->formatCurrency($i['current_balance'], 'USD'));
            printf("\n");
        }
    }

    public function audit()
    {
        foreach ($this->audit['expense'] as $e) {
            printf("%03d,%4d-%02d,%s,%d,%s\n",
                $e['period'], $e['year'], $e['month'],
                $e['name'], $e['amount'], $e['status'],
            );
        }

        foreach ($this->audit['asset'] as $i) {
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
     * This is two passes:
     * 1) figure out the total expenses in the given period
     * 2) increasing balances to account for inflation
     */
    private function getExpenseForPeriod(int $year, int $month): float
    {
        $expenses = $this->tallyExpenses($year, $month);
        $this->applyInflation();
        return $expenses;
    }

    /**
     * Adjust assets per period
     * This is two passes:
     * 1) reducing one or more balances per the $expense per period
     * 2) increasing all balances to account for interest earned
     */
    private function adjustAssetForPeriod(int $year, int $month, float $expense): bool
    {
        if ($this->makeWithdrawals($year, $month, $expense)) {
            $this->earnInterest();
            return true;
        } else {
            return false;
        }
    }

    private function tallyExpenses(int $year, int $month): float
    {
        // Activate expenses based on current period
        $i = 0;
        foreach ($this->expense as $expense) {
            // If we hit a planned expense, see if it's time to activate it
            if ($expense['status'] === 'planned') {
                if (($year >= $expense['begin_year']) && ($month >= $expense['begin_month'])) {
                    $this->log->debug("Activating expense {$expense['name']}, in year $year month $month, as planned from the start");
                    $this->expense[$i]['status'] = 'active';
                }
            }
            $i++;
        };

        // Now get amounts, drawing from every participating expense
        $total = 0.00;
        $i = 0;
        foreach ($this->expense as $expense) {
            if ($expense['status'] === 'active') {
                $this->log->debug(sprintf('Adding expense %s, amount %d to period tally', $expense['name'], $expense['amount']));
                $this->audit['expense'][] = [
                    'period' => $this->currentPeriod,
                    'year' => $year,
                    'month' => $month,
                    'name' => $expense['name'],
                    'amount' => $expense['amount'],
                    'status' => $expense['status'],
                ];
                $total += $expense['amount'];
            }
            $i++;
        } 

        // Lastly, has it ended?
        $i = 0;
        foreach ($this->expense as $expense) {
            if ($expense['status'] === 'active') {
                if (($year >= $expense['end_year']) && ($month >= $expense['end_month'])) {
                    if ($expense['repeat_every'] === null) {
                        $this->log->debug("Ending expense {$expense['name']}, in year $year month $month, as planned from the start");
                        $this->expense[$i]['status'] = 'ended';
                    } else {
                        $this->log->info("Ending expense {$expense['name']}, in year $year month $month, but rescheduling again " . $expense['repeat_every'] . " months out");
                        $nextPeriod = $this->util->addMonths($expense['begin_year'], $expense['begin_month'], $expense['repeat_every']);
                        $this->expense[$i]['status'] = 'planned';
                        $this->expense[$i]['begin_year'] = $nextPeriod['year'];
                        $this->expense[$i]['begin_month'] = $nextPeriod['month'];
                        $this->expense[$i]['end_year'] = $nextPeriod['year'];
                        $this->expense[$i]['end_month'] = $nextPeriod['month'];
                    }
                }
            }
            $i++;
        } 

        return round($total, 2);
    }

    private function applyInflation()
    {
        for ($i = 0; $i < count($this->expense); $i++) {
            $this->expense[$i]['amount'] += $this->util->calculateInterest(
                $this->expense[$i]['amount'],
                $this->expense[$i]['inflation_rate']
            );
        }
    }

    /**
     * Withdraw money from fund(s) until expense is matched
     */
    private function makeWithdrawals(int $year, int $month, float $expense): bool
    {
        $total = 0;

        foreach ($this->asset as $asset) {
            $this->audit['asset'][] = [
                'period' => $this->currentPeriod,
                'year' => $year,
                'month' => $month,
                'name' => $asset['name'],
                'opening_balance' => $asset['opening_balance'],
                'current_balance' => $asset['current_balance'],
                'max_withdrawal' => $asset['max_withdrawal'],
                'status' => $asset['status'],
            ];
        }

        for ($i = 0; $i < count($this->asset); $i++) {

            $this->activateAssets($year, $month);

            if ($this->asset[$i]['status'] === 'active') {

                // Set withdrawal amount
                $amount = round(min(
                    $expense,                               // When the full expense can be pulled from the source
                    $this->asset[$i]['max_withdrawal'],     // If there's a max, cap this period's withdrawal
                    $this->asset[$i]['current_balance'],    // Sometimes the current balance is the most we can pull
                    ($expense - $total),                    // And sometimes it's just the small amount we need
                ), 2);

                if ($amount === 0.00) {
                    $this->log->debug("Got a withdrawal amount near zero while pulling from asset $i");
                    $this->log->debug("  Amount          = $amount");
                    $this->log->debug("  Target Expense  = $expense");
                    $this->log->debug("  Max Withdrawal  = " . $this->asset[$i]['max_withdrawal']);
                    $this->log->debug("  Current Balance = " . $this->asset[$i]['current_balance']);
                    $this->log->debug("  Top-Off amount  = " . ($expense - $total));
                    exit;
                }
                $this->log->debug("Pulling $amount to meet $expense from asset {$asset['name']} in $year-$month");
                $total = round($total + $amount, 2);

                // Reduce balance by withdrawal amount
                $this->asset[$i]['current_balance'] -= $amount;

                // If we just depleted this asset, activate the next one(s)
                if ($this->asset[$i]['current_balance'] <= 0) {
                    $this->asset[$i]['current_balance'] = 0;
                    $this->asset[$i]['status'] = 'depleted';
                }

                $this->log->debug("Current balance of asset {$asset['name']} is " . $this->asset[$i]['current_balance']);

                if ($total === $expense) {
                    // Just hack our way out of this
                    break;
                }
            }
        }

        if ($total < $expense) {
            $this->log->warn("Could not find enough money $total in $year-$month to cover expense $expense");
            $this->log->warn("Game over, man! Game over! What're we supposed to do now?");
            return false;
        }

        return true;
    }

    /**
     * Activate assets per plan
     */
    private function activateAssets(int $year, int $month)
    {
        // Activate withdrawals based on current period
        for ($i = 0; $i < count($this->asset); $i++) {
            // If we hit an untapped asset, see if it's time to tap it
            if ($this->asset[$i]['status'] === 'untapped') {
                // Two ways to tap!
                // 1. It can follow a previously-depleted asset
                if ($this->asset[$i]['begin_after'] !== null) {
                    if ($this->asset[$this->asset[$i]['begin_after']]['status'] === 'depleted') {
                        $this->log->debug("Activating asset {$this->asset[$i]['name']}, in year $year month $month, after previous asset depleted");
                        $this->asset[$i]['status'] = 'active';
                    }
                }
                // 2. It can begin during a specified year and month
                if ($this->asset[$i]['begin_after'] === null) {
                    if (($year >= $this->asset[$i]['begin_year']) && ($month >= $this->asset[$i]['begin_month'])) {
                        $this->log->debug("Activating asset {$this->asset[$i]['name']}, in year $year month $month, as planned from the start");
                        $this->asset[$i]['status'] = 'active';
                    }
                }
            }
        }
    }

    /**
     * Loop through each asset and add interest
     */
    private function earnInterest()
    {
        for ($i = 0; $i < count($this->asset); $i++) {
            if ($this->asset[$i]['current_balance'] > 0) {
                $this->asset[$i]['current_balance'] += $this->util->calculateInterest(
                    $this->asset[$i]['current_balance'],
                    $this->asset[$i]['apr']
                );
            }
        }
    }

}
