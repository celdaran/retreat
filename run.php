<?php

require __DIR__ . '/vendor/autoload.php';

use App\Service\Engine\Engine;
use App\Service\CLI;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$cli = new CLI();

$engine = new Engine(
    $cli->getExpenseScenario(),
    $cli->getAssetScenario(),
    $cli->getIncomeScenario(),
    $cli->getTaxRate(),
);

$success = $engine->run($cli->getDuration(), $cli->getStartYear(), $cli->getStartMonth());
if (!$success) {
    echo "Something went wrong. Starting audit...\n";
    $engine->audit();
}
$engine->render();
// $engine->report();
