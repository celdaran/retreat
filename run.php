<?php

require_once('Service/Engine/Engine.php');
require_once('Service/Database/Income.php');
require_once('Service/Database/Expense.php');

use Service\Engine;

$engine = new Service\Engine\Engine();
$engine->run(2026, 2, 60);
$engine->render();
