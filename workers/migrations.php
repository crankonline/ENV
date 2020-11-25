<?php
namespace Requisites;

use Unikum\Core\Migration;

chdir(dirname(__DIR__));
require 'core/configuration.php';

$argument = $argv[1] ?? 'help';

switch ($argument) {
    case 'migrate':
        Migration::migrate();
        break;

    case 'down':
        Migration::downgrade();
        break;

    case 'generate':
        Migration::generate();
        break;

    default:
        echo "\e[93mAvailable arguments:\e[39m " . PHP_EOL;
        echo "   migrate  - Start migrations" . PHP_EOL;
        echo "   down     - Downgrade to last migration" . PHP_EOL;
        echo "   generate - Generate new migration class" . PHP_EOL;
        break;
}
echo PHP_EOL;
