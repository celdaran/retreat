<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Service\Engine\Engine;

$engine = new Engine('20230426');
$engine->run(2026, 1, 30*12);
$engine->report();
