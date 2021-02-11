<?php
/**
 * Reregister
 */
namespace Environment;

require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;

if(!isset($_GET['service'])){
    http_response_code(404);
    exit;
}

switch($_GET['service']){
    case 'Users':
    case 'UsageStatuses':
    case 'Settlements':
    case 'Activities':
    case 'Representatives':
    case 'Tokens':
    case 'Statements':
    case 'Nwa':
    case 'SochiExporters':
        break;

    default:
        http_response_code(404);
        exit;
}

$module = __NAMESPACE__ . '\\Services\\' . $_GET['service'];
$module::run();

Connections::shutdown();
?>