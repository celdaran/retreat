<?php namespace App\Service\Data;

use App\Service\Data\Database;

class Scenario
{
    private Database $data;

    public function __construct()
    {
        $this->data = new Database();
        $this->data->connect($_ENV['DBHOST'], $_ENV['DBUSER'], $_ENV['DBPASS'], $_ENV['DBNAME']);
    }

    public function getScenario(string $scenarioName)
    {
        // Get the query
        $sql = $this->fetchQuery();

        // Get the data
        $rows = $this->data->select($sql, ['scenario_name' => $scenarioName]);

        if (count($rows) === 0) {
            die("Scenario $scenarioName not found\n");
        }

        return $rows;
    }

    public function getStart(?int $startYear, ?int $startMonth): array
    {
        if ($startYear === null) {
            $sql = "SELECT min(begin_year) AS startYear FROM expense";
            $rows = $this->data->select($sql);
            $startYear = $rows[0]['startYear'];
        }

        if ($startMonth === null) {
            $sql = "SELECT min(begin_month) AS startMonth FROM expense WHERE begin_year = :begin_year";
            $rows = $this->data->select($sql, ['begin_year' => $startYear]);
            $startMonth = $rows[0]['startMonth'];
        }

        return [$startYear, $startMonth];
    }

}
