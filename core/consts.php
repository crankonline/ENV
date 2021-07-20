<?php
define('DS', DIRECTORY_SEPARATOR);

switch(php_sapi_name()){
    case 'cli':
        define('EXECUTION_MODE', 'CLI');

        define('SYSTEM_ROOT', dirname(__DIR__) . DS);
    break;

    default:
        define('EXECUTION_MODE', 'WEBSERVER');

        define(
            'SYSTEM_SCHEMA',
            empty($_SERVER['HTTPS']) || (strtolower($_SERVER['HTTPS']) == 'off')
                ? 'http'
                :'https'
        );

        define('SYSTEM_HOST', SYSTEM_SCHEMA . '://' . $_SERVER['SERVER_NAME'] . '/');
        define('SYSTEM_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
    break;
}

define('PATH_CORE', SYSTEM_ROOT . 'core' . DS);
define('PATH_FILESHARE', SYSTEM_ROOT . 'fileshare' . DS);
define('PATH_TEMPLATES', SYSTEM_ROOT . 'layouts' . DS);
define('PATH_RESOURCES', SYSTEM_ROOT . 'resources' . DS);
define('PATH_VENDORS', SYSTEM_ROOT . 'vendor' . DS);
define('PATH_WORKERS', SYSTEM_ROOT . 'workers' . DS);
define('PATH_LOGS', SYSTEM_ROOT . 'logs' . DS);

define('PATH_BASE_CLASSES', PATH_CORE . 'classes' . DS);
define('PATH_DERIVED_TRAITS', PATH_CORE . 'traits-derived' . DS);
define('PATH_DERIVED_CLASSES', PATH_CORE . 'classes-derived' . DS);
define('PATH_MODULES', PATH_CORE . 'modules' . DS);
define('PATH_JSON_SERVICES', PATH_CORE . 'services' . DS);
define('PATH_DATA_LAYERS', PATH_CORE . 'data-layers' . DS);
define('PATH_SOAP_CLIENTS', PATH_CORE . 'soap-clients' . DS);
define('PATH_SOAP_SERVICES', PATH_CORE . 'soap-services' . DS);
define('PATH_SOAP_TYPES', PATH_CORE . 'soap-types' . DS);
define('PATH_MIGRATIONS', PATH_CORE . 'migrations' . DS);
define('PATH_UI', PATH_CORE . 'ui' . DS);
define('PATH_DOCS', SYSTEM_ROOT . 'docs' . DS);


/***/

define('SESSION_INITIAL_NAME', 'session');
define('SESSION_EXPIRE_LIMIT', 60 * 60);

define('SESSION_ACTION_KEY', 'action');
define('SESSION_ACTION_CURRENT_KEY', 'current');
define('SESSION_ACTION_LAST_KEY', 'last');
define('SESSION_ACTION_INTERVAL_KEY', 'interval');

define('SESSION_USER_KEY', 'user');

define('HTTP_AUTH_1C_USR', 'sochi');
define('HTTP_AUTH_1C_PWD', 'ufvguygbvjvbugjsb6546fg964b96');

?>
