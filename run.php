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
            'defaultValue' => 'defaults to expense scenario',
        ],
    ]
);
$climate->arguments->parse();

$expenseScenario = $climate->arguments->get('expense');
$assetScenario = $climate->arguments->get('asset');

if ($assetScenario === 'defaults to expense scenario') {
    $assetScenario = $expenseScenario;
}

$engine = new Engine($expenseScenario, $assetScenario);
$success = $engine->run(2026, 1, 5*12);
if (!$success) {
    echo "Something went wrong. Starting audit...\n";
    $engine->audit();
}
$engine->render();
// $engine->report();
