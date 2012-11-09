<?php
/**
 * Created by JetBrains PhpStorm.
 * User: seb
 * Date: 11/9/12
 * Time: 12:27 PM
 * To change this template use File | Settings | File Templates.
 */

// ========== bootstrap ======
require __DIR__ . "/../../../../vendor/autoload.php";

use TestStackExample\Bootstrap;
use TestStackExample\Task\Runner;

Bootstrap::getInstance()
    ->init();

// ========== run ============

Runner::getInstance()->run();
