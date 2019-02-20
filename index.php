<?php
namespace Environment;

ini_set('display_errors', 1);

require 'core/configuration.php';

use Unikum\Core\Router as Router,
    Unikum\Core\Dbms\ConnectionManager as Connections;

Router::map(

    [
        'module' => 'Services',
        'condition' => function() {
            if (isset($_GET['view']))
                return $_GET['view'] == 'services';
        }
    ],
    [
        'condition' => function(){
            session_name(SESSION_INITIAL_NAME);
            session_start();

            if(empty($_SESSION)){
                session_destroy();

                return true;
            }

            $now = isset($_SERVER['REQUEST_TIME_FLOAT'])
                ? $_SERVER['REQUEST_TIME_FLOAT']
                : microtime(true);

            if(!isset($_SESSION[SESSION_ACTION_KEY])){
                $_SESSION[SESSION_ACTION_KEY] = [];
            }

            $action = &$_SESSION[SESSION_ACTION_KEY];

            if(!isset($action[SESSION_ACTION_CURRENT_KEY])){
                $action[SESSION_ACTION_CURRENT_KEY] = $now;
            }

            $action[SESSION_ACTION_LAST_KEY]     = $action[SESSION_ACTION_CURRENT_KEY];
            $action[SESSION_ACTION_INTERVAL_KEY] = $now - $action[SESSION_ACTION_LAST_KEY];

            $expired = $action[SESSION_ACTION_INTERVAL_KEY] > SESSION_EXPIRE_LIMIT;

            if($expired){
                session_unset();
                session_destroy();
            } else {
                $action[SESSION_ACTION_CURRENT_KEY] = $now;
            }

            return $expired;
        },

        'module' => 'Login'
    ], [
        'module' => 'Workspace'
    ]
);

$module = Router::navigate();


if($module){

    $module = __NAMESPACE__ . '\\Modules\\' . $module;

    $module::run();

    Connections::shutdown();
} else {
    http_response_code(404);
}
?>
