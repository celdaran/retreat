<?php namespace Service\Data;

use Service\Data\Database;

/**
 * Base class for accounts
 * There are two account types "assets" and "expenses." (For now, debts
 * and liabilities are considered expenses.)
 */
class Account
{
    private Database $data;

    public function __construct()
    {
        $this->data = new Database();
    }

    /**
     * Fetch a scenario
     * This supports parent/child scenarios, where a child
     * can inherit the attriutes of its parent (but not recursively).
     */
    protected function getScenario(string $scenarioName = 'base')
    {
        // $income = $this->data->select("SELECT * FROM income WHERE scenario = $scenarioName");
        // maybe do stuff to $income (not "maybe" because we now have inheritance)
        // return $income;
        $scenarios = $this->getScenarios();

        if ($scenarios[$scenarioName]['parent'] === null) {
            // If there's no parent, then return the current scenario as is
            return $scenarios[$scenarioName]['scenario'];
        } else {
            // If there's a parent, then the parent and current scenario must be merged
            $parentScenarioName = $scenarios[$scenarioName]['parent'];
            $parentScenario = $scenarios[$parentScenarioName]['scenario'];
            $childScenario = $scenarios[$scenarioName]['scenario'];

            // no, start with nothing
            $mergedScenario = [];
            foreach ($parentScenario as $scenario) {
                $mergedScenario[] = $this->merge($scenario, $childScenario);
            }

            return $mergedScenario;
        }
    }

    /**
     * Core parent/child merge function
     */
    private function merge(array $parentScenario, array $children)
    {
        $merge = [];

        foreach ($children as $childScenario) {
            if ($childScenario['name'] === $parentScenario['name']) {
                return array_merge($parentScenario, $childScenario);
            }
        }

        return $parentScenario;
    }

}
