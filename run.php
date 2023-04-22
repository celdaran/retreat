<?php

require_once('Service/Engine/Engine.php');
require_once('Service/Data/Database.php');
require_once('Service/Data/Account.php');
require_once('Service/Data/Income.php');
require_once('Service/Data/Expense.php');
require_once('Service/Log.php');

use Service\Engine\Engine;

$engine = new Engine(); //'alt', 'alt');
$engine->run(2026, 1, 12);
$engine->report();
