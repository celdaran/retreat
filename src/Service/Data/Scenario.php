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

}
