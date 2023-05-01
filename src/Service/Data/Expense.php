<?php namespace App\Service\Data;

class Expense extends Scenario
{
    protected function fetchQuery(): string
    {
        return "
            SELECT
                e.expense_name AS name,
                SUBSTRING_INDEX(group_concat(e.amount ORDER BY e.expense_id), ',', -1) AS amount,
                SUBSTRING_INDEX(group_concat(e.inflation_rate ORDER BY e.inflation_rate), ',', -1) AS inflation_rate,
                SUBSTRING_INDEX(group_concat(e.begin_year ORDER BY e.expense_id), ',', -1) AS begin_year,
                SUBSTRING_INDEX(group_concat(e.begin_month ORDER BY e.expense_id), ',', -1) AS begin_month,
                SUBSTRING_INDEX(group_concat(e.end_year ORDER BY e.expense_id), ',', -1) AS end_year,
                SUBSTRING_INDEX(group_concat(e.end_month ORDER BY e.expense_id), ',', -1) AS end_month,
                SUBSTRING_INDEX(group_concat(e.repeat_every ORDER BY e.expense_id), ',', -1) AS repeat_every,
                'planned' AS status
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

}
