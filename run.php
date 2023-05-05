<?php

require __DIR__ . '/vendor/autoload.php';

use App\Service\Engine\Engine;
use League\CLImate\CLImate;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// climate stuff TODO: MOVE SOMEWHERE

$climate = new CLImate();
$climate->arguments->add(
    [
        'help' => [
            'prefix' => '?',
            'longPrefix' => 'help',
            'noValue'     => true,
        ],
        'expense' => [
            'prefix' => 'e',
            'longPrefix' => 'expense',
            'description' => 'Specify the name of the expense scenario',
            'defaultValue' => 'base',
        ],
        'asset' => [
            'prefix' => 'a',
            'longPrefix' => 'asset',
            'description' => 'Specify the name of the asset scenario',
            'defaultValue' => 'same as expense',
        ],
        'startYear' => [
            'prefix' => 'y',
            'longPrefix' => 'year',
            'description' => 'The start year of the simulation',
            'defaultValue' => null,
        ],
        'startMonth' => [
            'prefix' => 'm',
            'longPrefix' => 'month',
            'description' => 'The start month of the simulation',
            'defaultValue' => null,
        ],
        'duration' => [
            'prefix' => 'd',
            'longPrefix' => 'duration',
            'description' => 'The duration, in months, of the simulation',
            'defaultValue' => 360,
        ],
        'adjust' => [
            'prefix' => 'a',
            'longPrefix' => 'adjust',
            'description' => 'Adjust the start year and month to match the simulation start year and month. By default, each expense and asset begins in a pre-defined period, persisted to the database. However, for non-fixed-period expenses and assets, this value can be overridden by the simulation\'s start period.',
            'noValue'     => true,
        ],
    ]
);
$climate->arguments->parse();

if ($climate->arguments->get('help')) {
    $climate->usage();
    exit;
}

$expenseScenario = $climate->arguments->get('expense');
$assetScenario = $climate->arguments->get('asset');
$startYear = $climate->arguments->get('startYear');
$startMonth = $climate->arguments->get('startMonth');
$duration = $climate->arguments->get('duration');

if ($assetScenario === 'same as expense') {
    $assetScenario = $expenseScenario;
}

if ($startYear === "") {
    $startYear = null;
}

if ($startMonth === "") {
    $startMonth = null;
}

$engine = new Engine($expenseScenario, $assetScenario);
$success = $engine->run($duration, $startYear, $startMonth);
if (!$success) {
    echo "Something went wrong. Starting audit...\n";
    $engine->audit();
}
$engine->render();
// $engine->report();
