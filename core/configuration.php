<?php
namespace Environment;

require_once 'core/consts.php';
require_once 'core/classes/AutoLoaders/Psr4.php';
require_once 'vendor/autoload.php';

use Unikum\Core\AutoLoaders\Psr4 as Loader,
    Unikum\Core\Dbms\ConnectionManager as Connections;

$dotenv = \Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

define('API_SUBSCRIBER_TOKEN', $_ENV['configutarion_API_SUBSCRIBER_TOKEN']);
define('TUNDUK_SUBSCRIBER_TOKEN', $_ENV['configutarion_Tunduk_SUBSCRIBER_TOKEN']);

Loader::register();

Loader::map('Unikum\\Core', PATH_BASE_CLASSES);

Loader::map(__NAMESPACE__ . '\\Core', PATH_DERIVED_CLASSES);
Loader::map(__NAMESPACE__ . '\\Traits', PATH_DERIVED_TRAITS);
Loader::map(__NAMESPACE__ . '\\Modules', PATH_MODULES);
Loader::map(__NAMESPACE__ . '\\Services', PATH_JSON_SERVICES);
Loader::map(__NAMESPACE__ . '\\DataLayers', PATH_DATA_LAYERS);
Loader::map(__NAMESPACE__ . '\\Vendors', PATH_VENDORS);
Loader::map(__NAMESPACE__ . '\\Soap\\Clients', PATH_SOAP_CLIENTS);
Loader::map(__NAMESPACE__ . '\\Soap\\Services', PATH_SOAP_SERVICES);
Loader::map(__NAMESPACE__ . '\\Soap\\Types', PATH_SOAP_TYPES);

\Sentry\init(['dsn' => 'http://'.$_ENV['configuration_sentry_dsn'].'@sentry.dostek.kg/5' ]);



ini_set('soap.wsdl_cache_enabled', 0);

Connections::configure(
    'Environment',
    [
        'dsn'      => $_ENV['configuration_connection_Environment_dsn'],
        'user'     => $_ENV['configuration_connection_Environment_user'],
        'password' => $_ENV['configuration_connection_Environment_password']
    ]
);

Connections::configure(
    'Requisites',
    [
        'dsn'      => $_ENV['configuration_connection_Requisites_dsn'],
        'user'     => $_ENV['configuration_connection_Requisites_user'],
        'password' => $_ENV['configuration_connection_Requisites_password']
    ]
);

Connections::configure(
    'Reregister',
    [
        'dsn'      => $_ENV['configuration_connection_Reregister_dsn'],
        'user'     => $_ENV['configuration_connection_Reregister_user'],
        'password' => $_ENV['configuration_connection_Reregister_password']
    ]
);

Connections::configure(
    'Api',
    [
        'dsn'      => $_ENV['configuration_connection_Api_dsn'],
        'user'     => $_ENV['configuration_connection_Api_user'],
        'password' => $_ENV['configuration_connection_Api_password']
    ]
);

Connections::configure(
    'Sochi',
    [
        'dsn'      => $_ENV['configuration_connection_Sochi_dsn'],
        'user'     => $_ENV['configuration_connection_Sochi_user'],
        'password' => $_ENV['configuration_connection_Sochi_password']
    ]
);

Connections::configure(
    'Billing',
    [
        'dsn'      => $_ENV['configuration_connection_Billing_dsn'],
        'user'     => $_ENV['configuration_connection_Billing_user'],
        'password' => $_ENV['configuration_connection_Billing_password']
    ]
);

Connections::configure(
    'SeoBaseWeb',
    [
        'dsn'      => $_ENV['configuration_connection_SeoBaseWeb_dsn'],
        'user'     => $_ENV['configuration_connection_SeoBaseWeb_user'],
        'password' => $_ENV['configuration_connection_SeoBaseWeb_password']
    ]
);

Connections::configure(
    'OnlineStatements',
    [
        'dsn'      => $_ENV['configuration_connection_OnlineStatements_dsn'],
        'user'     => $_ENV['configuration_connection_OnlineStatements_user'],
        'password' => $_ENV['configuration_connection_OnlineStatements_password']
    ]
);

Connections::configure(
    'OnlineStatementFiles',
    [
        'dsn'      => $_ENV['configuration_connection_OnlineStatementFiles_dsn'],
        'user'     => $_ENV['configuration_connection_OnlineStatementFiles_user'],
        'password' => $_ENV['configuration_connection_OnlineStatementFiles_password']
    ]
);

Connections::configure(
    'Egrse',
    [
        'dsn'      => $_ENV['configuration_connection_Egrse_dsn'],
        'user'     => $_ENV['configuration_connection_Egrse_user'],
        'password' => $_ENV['configuration_connection_Egrse_password']
    ]
);

Connections::configure(
    'Sf',
    [
        'dsn'      => $_ENV['configuration_connection_Sf_dsn'],
        'user'     => $_ENV['configuration_connection_Sf_user'],
        'password' => $_ENV['configuration_connection_Sf_password']
    ]
);

Connections::configure(
    'Sti',
    [
        'dsn'      => $_ENV['configuration_connection_Sti_dsn'],
        'user'     => $_ENV['configuration_connection_Sti_user'],
        'password' => $_ENV['configuration_connection_Sti_password']
    ]
);

Connections::configure(
    'Nsc',
    [
        'dsn'      => $_ENV['configuration_connection_Nsc_dsn'],
        'user'     => $_ENV['configuration_connection_Nsc_user'],
        'password' => $_ENV['configuration_connection_Nsc_password']
    ]
);

Connections::configure(
	'FileStore',
	[
        'dsn'      => $_ENV['configuration_connection_FileStore_dsn'],
        'user'     => $_ENV['configuration_connection_FileStore_user'],
        'password' => $_ENV['configuration_connection_FileStore_password']
	]
);

Connections::configure(
    'SfArchive',
    [
        'dsn'      => $_ENV['configuration_connection_SfArchive_dsn'],
        'user'     => $_ENV['configuration_connection_SfArchive_user'],
        'password' => $_ENV['configuration_connection_SfArchive_password']
    ]
);


