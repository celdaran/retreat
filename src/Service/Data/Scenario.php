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
        return $this->data->select($sql, ['scenario_name' => $scenarioName]);
    }

}
