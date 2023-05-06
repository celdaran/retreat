<?php namespace App\Service\Data;

use App\Service\Engine\Period;
use App\Service\Log;

class Scenario
{
    private Database $data;
    private Log $log;

    public function __construct()
    {
        $this->data = new Database();
        $this->data->connect($_ENV['DBHOST'], $_ENV['DBUSER'], $_ENV['DBPASS'], $_ENV['DBNAME']);

        $this->log = new Log();
        $this->log->setLevel($_ENV['LOG_LEVEL']);
    }

    public function getData(): Database
    {
        return $this->data;
    }

    public function getLog(): Log
    {
        return $this->log;
    }

    protected function getRowsForScenario(string $scenarioName, string $sql): array
    {
        // Get the data
        $rows = $this->data->select($sql, ['scenario_name' => $scenarioName]);

        if (count($rows) === 0) {
            die("Scenario $scenarioName not found\n");
        }

        return $rows;
    }

}
