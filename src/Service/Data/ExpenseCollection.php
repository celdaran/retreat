<?php namespace App\Service\Data;

use App\Service\Engine\Expense;
use App\Service\Engine\Money;
use App\Service\Engine\Period;
use App\Service\Engine\Util;

class ExpenseCollection extends Scenario
{
    private array $expenses = [];

    /**
     * Load a scenario
     *
     * @param string $scenarioName
     */
    public function loadScenario(string $scenarioName)
    {
         $rows = parent::getRowsForScenario($scenarioName, $this->fetchQuery());
         $this->expenses = $this->transform($rows);
    }

    /**
     * Primarily for unit testing
     * @param string $scenarioName
     * @param array $scenarios
     */
    public function loadScenarioFromMemory(string $scenarioName, array $scenarios)
    {
        $rows = $scenarios[$scenarioName];
        $this->expenses = $this->transform($rows);
    }

    public function getExpenses(): array
    {
        return $this->expenses;
    }

    /**
     * Fetch initial period from database based on year and month
     *
     * @param int|null $startYear
     * @param int|null $startMonth
     * @return Period
     */
    public function getStart(?int $startYear, ?int $startMonth): Period
    {
        if ($startYear === null) {
            $sql = "SELECT min(begin_year) AS startYear FROM expense";
            $rows = $this->getData()->select($sql);
            $startYear = $rows[0]['startYear'];
        }

        if ($startMonth === null) {
            $sql = "SELECT min(begin_month) AS startMonth FROM expense WHERE begin_year = :begin_year";
            $rows = $this->getData()->select($sql, ['begin_year' => $startYear]);
            $startMonth = $rows[0]['startMonth'];
        }

        return new Period($startYear, $startMonth);
    }

    public function tallyExpenses(Period $period): Money
    {
        // Activate expenses based on period
        /** @var Expense $expense */
        foreach ($this->expenses as $expense) {
            // If we hit a planned expense, see if it's time to activate it
            if ($expense->timeToActivate($period)) {
                $msg = sprintf('Activating expense "%s" in year %4d-%02d, as planned from the start',
                    $expense->name(),
                    $period->getYear(),
                    $period->getMonth(),
                );
                $this->getLog()->debug($msg);
                $expense->markActive();
            }
        }

        // Now get amounts, drawing from every participating expense
        $total = new Money();
        foreach ($this->expenses as $expense) {
            if ($expense->isActive()) {
                $msg = sprintf('Adding expense "%s", amount %s to period %s tally',
                    $expense->name(),
                    $expense->amount()->formatted(),
                    $period->getCurrentPeriod(),
                );
                $this->getLog()->debug($msg);
                /*
                $this->audit['expense'][] = [
                    'period' => $period->getCurrentPeriod(),
                    'year' => $period->getYear(),
                    'month' => $period->getMonth(),
                    'name' => $expense->name(),
                    'amount' => $expense->amount()->formatted(),
                    'status' => $expense->status(),
                ];
                */
                $total->add($expense->amount()->value());
            }
        }

        // Lastly, has it ended?
        foreach ($this->expenses as $expense) {
            if ($expense->timeToEnd($period)) {
                if ($expense->repeatEvery() === null) {
                    $msg = sprintf('Ending expense "%s" in %4d-02%d, as planned from the start',
                        $expense->name(),
                        $period->getYear(),
                        $period->getMonth(),
                    );
                    $this->getLog()->debug($msg);
                    $expense->markEnded();
                } else {
                    $msg = sprintf('Ending expense "%s" in %4d-%02d, but rescheduling %s months out',
                        $expense->name(),
                        $period->getYear(),
                        $period->getMonth(),
                        $expense->repeatEvery(),
                    );
                    $this->getLog()->debug($msg);
                    $nextPeriod = $period->addMonths($expense->beginYear(), $expense->beginMonth(), $expense->repeatEvery());
                    $expense->markPlanned();
                    $expense->setBeginYear($nextPeriod->getYear());
                    $expense->setBeginMonth($nextPeriod->getMonth());
                    $expense->setEndYear($nextPeriod->getYear());
                    $expense->setEndMonth($nextPeriod->getMonth());
                }
            }
        }

        return $total;
    }

    public function applyInflation()
    {
        /** @var Expense $expense */
        foreach ($this->expenses as $expense) {
            $interest = Util::calculateInterest($expense->amount()->value(), $expense->inflationRate());
            $expense->increaseAmount($interest);
        }
    }

    /**
     * Return SQL required to fetch a scenario from the database
     *
     * @return string
     */
    private function fetchQuery(): string
    {
        return "
            SELECT
                e.expense_name AS expense_name,
                SUBSTRING_INDEX(group_concat(e.amount ORDER BY e.expense_id), ',', -1) AS amount,
                SUBSTRING_INDEX(group_concat(e.inflation_rate ORDER BY e.inflation_rate), ',', -1) AS inflation_rate,
                SUBSTRING_INDEX(group_concat(e.begin_year ORDER BY e.expense_id), ',', -1) AS begin_year,
                SUBSTRING_INDEX(group_concat(e.begin_month ORDER BY e.expense_id), ',', -1) AS begin_month,
                SUBSTRING_INDEX(group_concat(e.end_year ORDER BY e.expense_id), ',', -1) AS end_year,
                SUBSTRING_INDEX(group_concat(e.end_month ORDER BY e.expense_id), ',', -1) AS end_month,
                SUBSTRING_INDEX(group_concat(e.repeat_every ORDER BY e.expense_id), ',', -1) AS repeat_every,
                SUBSTRING_INDEX(group_concat(e.fixed_period ORDER BY e.expense_id), ',', -1) AS fixed_period
            FROM (
                SELECT
                    e.*
                FROM scenario s1
                JOIN expense e ON e.scenario_id = s1.scenario_id
                WHERE s1.scenario_name = :scenario_name
                AND s1.account_type_id = 1
                UNION
                SELECT
                    e.*
                FROM scenario s1
                LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
                JOIN expense e ON e.scenario_id = s2.scenario_id
                WHERE s1.scenario_name = :scenario_name
                AND s1.account_type_id = 1
            
                ORDER BY expense_id
            ) AS e
            GROUP BY e.expense_name
            ORDER BY e.expense_name
        ";
    }

    /**
     * Transform fetched-rows into an array of objects
     *
     * @param array $rows
     * @return array
     */
    private function transform(array $rows): array
    {
        $collection = [];

        foreach ($rows as $row) {
            $expense = new Expense();
            $expense
                ->setName($row['expense_name'])
                ->setAmount(new Money((float)$row['amount']))
                ->setInflationRate($row['inflation_rate'])
                ->setBeginYear($row['begin_year'])
                ->setBeginMonth($row['begin_month'])
                ->setEndYear($row['end_year'])
                ->setEndMonth($row['end_month'])
                ->setRepeatEvery($row['repeat_every'])
                ->markPlanned()
            ;
            $collection[] = $expense;
        }

        return $collection;
    }

}
