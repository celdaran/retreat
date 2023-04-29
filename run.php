<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Service\Engine\Engine;

$engine = new Engine('20230426');
$success = $engine->run(2026, 1, 5*12);
if (!$success) {
    echo "Something went wrong. Starting audit...\n";
    $engine->audit();
}
$engine->render();
// $engine->report();
