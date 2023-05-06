<?php namespace App\Service\Engine;

use App\Service\Data\ExpenseCollection;
use App\Service\Data\AssetCollection;
use App\Service\Log;

class Engine
{
    private string $expenseScenarioName;
    private string $assetScenarioName;

    private ExpenseCollection $expenseCollection;
    private AssetCollection $assetCollection;

    private array $plan;
    private array $audit;

    private Log $log;

    private Period $currentPeriod;
    private Money $annualIncome;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
    /**
     * Constructor
     * Optionally pass in asset and/or expense scenario names and this
     * method preps the engine for running a simulation and rendering
     * the results.
     */
    public function __construct(string $expenseScenarioName = 'base', string $assetScenarioName = null)
    {
        // Get scenario names
        $this->expenseScenarioName = $expenseScenarioName;
        $this->assetScenarioName = ($assetScenarioName === null) ? $expenseScenarioName : $assetScenarioName;

        // Instantiate main classes
        $this->expenseCollection = new ExpenseCollection();
        $this->assetCollection = new AssetCollection();

        $this->plan = [];
        $this->audit = [];

        $this->log = new Log();
        $this->log->setLevel($_ENV['LOG_LEVEL']);

        $this->annualIncome = new Money();

        $this->audit = [
            'expense' => [],
            'asset' => [],
        ];
    }

    /**
     * Core function of the engine: to take all inputs and generate a plan
     */
    public function run(int $periods, ?int $startYear, ?int $startMonth): bool
    {
        // Load scenarios
        // A "scenario" is an array of like items (an array of expenses, array of assets)
        $this->expenseCollection->loadScenario($this->expenseScenarioName);
        $this->assetCollection->loadScenario($this->assetScenarioName);

        // Adjust in-memory scenarios based on requested start period
        // TODO: make this optional
        /*
        $this->adjustScenario($this->expense, $startYear, $startMonth);
        $this->adjustScenario($this->asset, $startYear, $startMonth);
        */

        // Track period (year and month)
        $this->currentPeriod = $this->expenseCollection->getStart($startYear, $startMonth);

        // Loop until the requested number of months have passed.
        while ($periods > 0) {

            // Start by tallying all expenses for period
            $expense = $this->getExpensesForPeriod();

            // Keep a running total for tax purposes
            $this->annualIncome->add($expense->value());
            $this->log->debug("Annual income in period {$this->currentPeriod->getCurrentPeriod()} is {$this->annualIncome->value()}");

            // If we're in the fourth period, calculate taxes
            // Note: this has issues. But it's good enough
            if ($this->currentPeriod->getCurrentPeriod() % 12 === 4) {
                $taxAmount = $this->annualIncome * 0.18;
                $expense->add($taxAmount);
                $this->log->debug("Paying income tax of $taxAmount in period {$this->currentPeriod->getCurrentPeriod()}");
                $this->annualIncome->assign(0.00);
            }

            // Now adjust assets based on current expenses
            if (!$this->adjustAssetForPeriod($expense)) {
                return false;
            }

            // Lastly record the plan
            $planEntry = [
                'period' => $this->currentPeriod->getCurrentPeriod(),
                'year' => $this->currentPeriod->getYear(),
                'month' => $this->currentPeriod->getMonth(),
                'expense' => $expense,
                'assets' => $this->assetCollection->getBalances(),
            ];
            $this->plan[] = $planEntry;

            // Next period
            $this->currentPeriod->advance();
            $periods--;
        }

        return true;
    }

    public function render($format = 'csv')
    {
        printf("%s,%s,%s,,", 'period', 'month', 'expense');
        $i = 0;
        foreach ($this->plan as $p) {
            if ($i === 0) {
                if (count($p['assets']) > 0) {
                    foreach (array_keys($p['assets']) as $assetName) {
                        printf("\"%s\",", addslashes($assetName));
                    }
                }
                print("total assets\n");
            }
            $totalAssets = 0.00;
            printf("%03d,%4d-%02d,%.2f,,", $p['period'], $p['year'], $p['month'], $p['expense']->value());
            foreach ($p['assets'] as $asset) {
                printf("%.2f,", $asset);
                $totalAssets += $asset;
            }
            print("$totalAssets\n");
            $i++;
        }
    }

    public function report()
    {
        /** @var Asset $asset */
        foreach ($this->assetCollection->getAssets() as $asset) {
            printf("Asset: %s\n", $asset->name());
            printf("  Current balance: %s\n", $asset->currentBalance()->formatted());
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

//    private function adjustScenario(array &$scenario, int $startYear, int $startMonth)
//    {
//        for ($i = 0; $i < count($scenario); $i++) {
//            if ($scenario[$i]['fixed_period'] !== 1) {
//                // If it's not fixed, then it's subject to adjustment
//                $scenario[$i]['begin_year'] = $startYear;
//                $scenario[$i]['begin_month'] = $startMonth;
//            }
//        }
//    }

    /**
     * Get expenses for a given period
     * This is two passes:
     * 1) figure out the total expenses in the given period
     * 2) increasing balances to account for inflation
     */
    private function getExpensesForPeriod(): Money
    {
        $expenses = $this->expenseCollection->tallyExpenses($this->currentPeriod);
        $this->expenseCollection->applyInflation();
        return $expenses;
    }

    /**
     * Adjust assets per period
     * This is two passes:
     * 1) reducing one or more balances per the $expense per period
     * 2) increasing all balances to account for interest earned
     */
    private function adjustAssetForPeriod(Money $expense): bool
    {
        if ($this->assetCollection->makeWithdrawals($this->currentPeriod, $expense)) {
            $this->assetCollection->earnInterest();
            return true;
        } else {
            return false;
        }
    }

}
