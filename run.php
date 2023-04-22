<?php

require __DIR__ . '/vendor/autoload.php';

use App\Service\Engine\Engine;

$engine = new Engine(); //'alt', 'alt');
$engine->run(2026, 1, 12);
$engine->report();
