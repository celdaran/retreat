<?php namespace App\Service\Data;

use App\Service\Engine\Income;
use App\Service\Engine\Money;
use App\Service\Engine\Period;
use App\Service\Engine\Util;

class IncomeCollection extends Scenario
{
    private array $income = [];

    /**
     * Load a scenario
     *
     * @param string $scenarioName
     */
    public function loadScenario(string $scenarioName)
    {
         $rows = parent::getRowsForScenario($scenarioName, $this->fetchQuery());
         $this->income = $this->transform($rows);
    }

    /**
     * Primarily for unit testing
     * @param string $scenarioName
     * @param array $scenarios
     */
    public function loadScenarioFromMemory(string $scenarioName, array $scenarios)
    {
        $rows = $scenarios[$scenarioName];
        $this->income = $this->transform($rows);
    }

    public function getIncome(): array
    {
        return $this->income;
    }

    public function tallyIncome(Period $period): Money
    {
        // Activate incomes based on period
        /** @var Income $income */
        foreach ($this->income as $income) {
            // If we hit a planned income, see if it's time to activate it
            if ($income->timeToActivate($period)) {
                $msg = sprintf('Activating income "%s" in year %4d-%02d, as planned from the start',
                    $income->name(),
                    $period->getYear(),
                    $period->getMonth(),
                );
                $this->getLog()->debug($msg);
                $income->markActive();
            }
        }

        // Now get amounts, drawing from every participating income
        $total = new Money();
        foreach ($this->income as $income) {
            if ($income->isActive()) {
                $msg = sprintf('Adding income "%s", amount %s to period %s tally',
                    $income->name(),
                    $income->amount()->formatted(),
                    $period->getCurrentPeriod(),
                );
                $this->getLog()->debug($msg);
                $total->add($income->amount()->value());
            }
        }

        // Lastly, has it ended?
        foreach ($this->income as $income) {
            if ($income->timeToEnd($period)) {
                if ($income->repeatEvery() === null) {
                    $msg = sprintf('Ending income "%s" in %4d-02%d, as planned from the start',
                        $income->name(),
                        $period->getYear(),
                        $period->getMonth(),
                    );
                    $this->getLog()->debug($msg);
                    $income->markEnded();
                } else {
                    $msg = sprintf('Ending income "%s" in %4d-%02d, but rescheduling %s months out',
                        $income->name(),
                        $period->getYear(),
                        $period->getMonth(),
                        $income->repeatEvery(),
                    );
                    $this->getLog()->debug($msg);
                    $nextPeriod = $period->addMonths($income->beginYear(), $income->beginMonth(), $income->repeatEvery());
                    $income->markPlanned();
                    $income->setBeginYear($nextPeriod->getYear());
                    $income->setBeginMonth($nextPeriod->getMonth());
                    $income->setEndYear($nextPeriod->getYear());
                    $income->setEndMonth($nextPeriod->getMonth());
                }
            }
        }

        return $total;
    }

    public function applyInflation()
    {
        /** @var Income $income */
        foreach ($this->income as $income) {
            $interest = Util::calculateInterest($income->amount()->value(), $income->inflationRate());
            $income->increaseAmount($interest);
        }
    }

    public function getAmounts(bool $formatted = false): array
    {
        $amounts = [];
        /** @var Income $income */
        foreach ($this->income as $income) {
            $amounts[$income->name()] = $formatted ?
                $income->amount()->formatted() :
                $income->amount()->value();
        }
        return $amounts;
    }

    /**
     * Return SQL required to fetch a scenario from the database
     *
     * @return string
     */
    private function fetchQuery(): string
    {
        return file_get_contents(__DIR__ . '/../../../db/income-query.sql');
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
            $income = new Income();
            $income
                ->setName($row['income_name'])
                ->setAmount(new Money((float)$row['amount']))
                ->setInflationRate($row['inflation_rate'])
                ->setBeginYear($row['begin_year'])
                ->setBeginMonth($row['begin_month'])
                ->setEndYear($row['end_year'])
                ->setEndMonth($row['end_month'])
                ->setRepeatEvery($row['repeat_every'])
                ->markPlanned()
            ;
            $collection[] = $income;
        }

        return $collection;
    }

}
