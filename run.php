<?php

require_once('Service/Engine/Engine.php');
require_once('Service/Data/Database.php');
require_once('Service/Data/Income.php');
require_once('Service/Data/Expense.php');
require_once('Service/Log.php');

use Service\Engine;

$engine = new Service\Engine\Engine();
$engine->run(2026, 1, 12);
$engine->render();
