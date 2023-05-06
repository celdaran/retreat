<?php namespace App\Service\Data;

use App\Service\Engine\Asset;
use App\Service\Engine\Money;
use App\Service\Engine\Period;
use App\Service\Engine\Util;

class AssetCollection extends Scenario
{
    private array $assets = [];

    public function loadScenario(string $scenarioName)
    {
         $rows = parent::getRowsForScenario($scenarioName, $this->fetchQuery());
         $this->assets = $this->transform($rows);
    }

    /**
     * Primarily for unit testing
     * @param array $scenario
     * @noinspection PhpUnused
     */
    public function setScenario(array $scenario)
    {
        $this->assets = $scenario;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Withdraw money from fund(s) until expense is matched
     */
    public function makeWithdrawals(Period $period, Money $expense): bool
    {
        $total = new Money();

        /*
        foreach ($this->assets as $asset) {
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
        */

        /** @var Asset $asset */
        foreach ($this->assets as $asset) {
//        for ($i = 0; $i < count($this->assets); $i++) {

            $this->activateAssets($period);

            if ($asset->isActive()) {
//            if ($this->assets[$i]['status'] === 'active') {

                // Set withdrawal amount
                $amount = new Money();
                $amount->assign(
                    min(
                        $expense->value(),                   // When the full expense can be pulled from the source
                        $asset->maxWithdrawal()->value(),    // If there's a max, cap this period's withdrawal
                        $asset->currentBalance()->value(),   // Sometimes the current balance is the most we can pull
                    )
                );

                if ($amount->le(0.00)) {
                    $this->getLog()->debug('Got a withdrawal amount of zero while pulling from asset "' . $asset->name() . '"');
                    $this->getLog()->debug('  Amount          = ' . $amount->formatted());
                    $this->getLog()->debug('  Target Expense  = ' . $expense->formatted());
                    $this->getLog()->debug('  Max Withdrawal  = ' . $asset->maxWithdrawal()->formatted());
                    $this->getLog()->debug('  Current Balance = ' . $asset->currentBalance()->formatted());
                    $this->getLog()->debug('  Top-Off amount  = ' . ($expense->value() - $total->value()));
                    $asset->markDepleted();
                }
                $msg = sprintf('Pulling %s to meet %s from asset "%s" in %4d-%02d',
                    $amount->formatted(),
                    $expense->formatted(),
                    $asset->name(),
                    $period->getYear(),
                    $period->getMonth(),
                );
                $this->getLog()->debug($msg);
                $total->add($amount->value());

                // Reduce balance by withdrawal amount
                $asset->currentBalance()->subtract($amount->value());

                $msg = sprintf('Current balance of asset "%s" is %s',
                    $asset->name(),
                    $asset->currentBalance()->formatted(),
                );
                $this->getLog()->debug($msg);

                if ($total->value() === $expense->value()) {
                    // Just hack our way out of this
                    break;
                }
            }
        }

        if ($total->value() < $expense->value()) {
            $msg = sprintf('Could not find enough money %s in %4d-%02d (period %d) to cover expense "%s"',
                $total->formatted(),
                $period->getYear(),
                $period->getMonth(),
                $period->getCurrentPeriod(),
                $expense->formatted(),
            );
            $this->getLog()->warn($msg);
            return false;
        }

        return true;
    }

    /**
     * Activate assets per plan
     */
    public function activateAssets(Period $period)
    {
        /** @var Asset $asset */
        foreach ($this->assets as $asset) {

            if ($asset->isUntapped()) {
                if ($asset->beginAfter() !== null) {
                    $beginAfterAsset = $this->getBeginAfter($asset->beginAfter());
                    if ($beginAfterAsset->isDepleted()) {
                        $msg = sprintf('Activating asset "%s", in %4d-%02d, after previous asset depleted',
                            $asset->name(),
                            $period->getYear(),
                            $period->getMonth(),
                        );
                        $this->getLog()->debug($msg);
                        $asset->markActive();
                    }

                } else {
                    if ($asset->timeToActivate($period)) {
                        $msg = sprintf('Activating asset "%s", in %4d-%02d, as planned from the start',
                            $asset->name(),
                            $period->getYear(),
                            $period->getMonth(),
                        );
                        $this->getLog()->debug($msg);
                        $asset->markActive();
                    }
                }
            }
        }
    }

    private function getBeginAfter(int $beginAfter): ?Asset
    {
        foreach ($this->assets as $asset) {
            if ($asset->id() === $beginAfter) {
                return $asset;
            }
        }
        return null;
    }

    /**
     * Loop through each asset and add interest
     */
    public function earnInterest()
    {
        /** @var Asset $asset */
        foreach ($this->assets as $asset) {
            if ($asset->canEarnInterest()) {
                $interest = Util::calculateInterest($asset->currentBalance()->value(), $asset->apr());
                $asset->increaseCurrentBalance($interest);
            }
        }
    }

    public function getBalances(bool $formatted = false): array
    {
        $assets = [];
        /** @var Asset $asset */
        foreach ($this->assets as $asset) {
            $assets[$asset->name()] = $formatted ?
                $asset->currentBalance()->formatted() :
                $asset->currentBalance()->value();
        }
        return $assets;
    }

    private function fetchQuery(): string
    {
        return "
            SELECT
                a.asset_id,
                a.asset_name,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS opening_balance,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS current_balance,
                SUBSTRING_INDEX(group_concat(a.max_withdrawal ORDER BY a.asset_id), ',', -1) AS max_withdrawal,
                SUBSTRING_INDEX(group_concat(a.apr ORDER BY a.asset_id), ',', -1) AS apr,
                SUBSTRING_INDEX(group_concat(a.begin_after ORDER BY a.asset_id), ',', -1) AS begin_after,
                SUBSTRING_INDEX(group_concat(a.begin_year ORDER BY a.asset_id), ',', -1) AS begin_year,
                SUBSTRING_INDEX(group_concat(a.begin_month ORDER BY a.asset_id), ',', -1) AS begin_month,
                SUBSTRING_INDEX(group_concat(a.fixed_period ORDER BY a.asset_id), ',', -1) AS fixed_period
            FROM (
                SELECT
                    a.*
                FROM scenario s1
                JOIN asset a ON a.scenario_id = s1.scenario_id
                WHERE s1.scenario_name = :scenario_name
                AND s1.account_type_id = 2
                UNION
                SELECT
                    a.*
                FROM scenario s1
                LEFT JOIN scenario s2 ON s1.scenario_parent_id = s2.scenario_id
                JOIN asset a ON a.scenario_id = s2.scenario_id
                WHERE s1.scenario_name = :scenario_name
                AND s1.account_type_id = 2
            
                ORDER BY asset_id
            ) AS a
            GROUP BY a.asset_id, a.asset_name
            ORDER BY a.asset_id, a.asset_name
        ";
    }

    private function transform(array $rows): array
    {
        $collection = [];

        foreach ($rows as $row) {
            $asset = new Asset();
            $asset
                ->setId($row['asset_id'])
                ->setName($row['asset_name'])
                ->setOpeningBalance(new Money((float)$row['opening_balance']))
                ->setCurrentBalance(new Money((float)$row['opening_balance']))
                ->setMaxWithdrawal(new Money((float)$row['max_withdrawal']))
                ->setApr($row['apr'])
                ->setBeginAfter($row['begin_after'])
                ->setBeginYear($row['begin_year'])
                ->setBeginMonth($row['begin_month'])
                ->markUntapped()
            ;
            $collection[] = $asset;
        }

        return $collection;
    }

}
