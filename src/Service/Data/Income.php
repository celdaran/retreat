<?php namespace App\Service\Data;

use App\Service\Data\Database;

class Income
{
    private Database $data;

    public function __construct()
    {
        $this->data = new Database();
        $this->data->connect($_ENV['DBHOST'], $_ENV['DBUSER'], $_ENV['DBPASS'], $_ENV['DBNAME']);
    }

    public function getIncome(string $scenarioName)
    {
        // Get the query
        $sql = $this->fetchAssetQuery();

        // Get the data
        $assets = $this->data->select($sql, ['scenario_name' => $scenarioName]);

        return $assets;
    }

    private function fetchAssetQuery()
    {
        $sql = "
            SELECT
                a.asset_name AS name,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS opening_balance,
                SUBSTRING_INDEX(group_concat(a.opening_balance ORDER BY a.asset_id), ',', -1) AS current_balance,
                SUBSTRING_INDEX(group_concat(a.max_withdrawal ORDER BY a.asset_id), ',', -1) AS max_withdrawal,
                SUBSTRING_INDEX(group_concat(a.apr ORDER BY a.asset_id), ',', -1) AS apr,
                SUBSTRING_INDEX(group_concat(a.begin_after ORDER BY a.asset_id), ',', -1) AS begin_after,
                SUBSTRING_INDEX(group_concat(a.begin_year ORDER BY a.asset_id), ',', -1) AS begin_year,
                SUBSTRING_INDEX(group_concat(a.begin_month ORDER BY a.asset_id), ',', -1) AS begin_month,
                'untapped' AS status
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
            GROUP BY a.asset_name
            ORDER BY a.asset_name
        ";

        return $sql;
    }

}
