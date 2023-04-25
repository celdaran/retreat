<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Service\Engine\Engine;

$engine = new Engine(); //'alt', 'alt');
$engine->run(2026, 1, 12);
$engine->report();
