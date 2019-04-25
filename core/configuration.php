<?php
namespace Environment;

require_once 'core/consts.php';
require_once 'core/classes/AutoLoaders/Psr4.php';

use Unikum\Core\AutoLoaders\Psr4 as Loader,
    Unikum\Core\Dbms\ConnectionManager as Connections;

Loader::register();

Loader::map('Unikum\\Core', PATH_BASE_CLASSES);

Loader::map(__NAMESPACE__ . '\\Core', PATH_DERIVED_CLASSES);
Loader::map(__NAMESPACE__ . '\\Modules', PATH_MODULES);
Loader::map(__NAMESPACE__ . '\\DataLayers', PATH_DATA_LAYERS);
Loader::map(__NAMESPACE__ . '\\Vendors', PATH_VENDORS);
Loader::map(__NAMESPACE__ . '\\Soap\\Clients', PATH_SOAP_CLIENTS);
Loader::map(__NAMESPACE__ . '\\Soap\\Services', PATH_SOAP_SERVICES);
Loader::map(__NAMESPACE__ . '\\Soap\\Types', PATH_SOAP_TYPES);

ini_set('soap.wsdl_cache_enabled', 0);

Connections::configure(
    'Environment',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=Env;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Requisites',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=RequisitesDosTek;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Reregister',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=Reregister;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Api',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=Api;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Sochi',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=rsvi_base;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Billing',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=billing_base;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'SeoBaseWeb',
    [
        'dsn'      => "pgsql:host=db.dostek.kg;port=5432;dbname=seo_base_web;options='--client_encoding=UTF8'",
        'user'     => 'seo_web',
        'password' => '7ee61dcbdcf8b8bfd68892fb245c89fc'
    ]
);

Connections::configure(
    'OnlineStatements',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=OnlineStatements;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'OnlineStatementFiles',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=OnlineStatementFiles;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Egrse',
    [
        'dsn'      => 'odbc:Driver=FreeTDS;ServerName=egrseServer;Database=statcom',
        'user'     => 'web',
        'password' => '123456'
    ]
);

Connections::configure(
    'Sarp',
    [
        'dsn'      => 'odbc:Driver=FreeTDS;ServerName=sarpServer;Database=SARP',
        'user'     => 'sarp',
        'password' => 'tbYRKla0AFOb0Ow1QL69'
    ]
);

Connections::configure(
    'Sf',
    [
        'dsn'      => "pgsql:host=reg.sf.kg;port=5432;dbname=Sf;options='--client_encoding=UTF8'",
        'user'     => 'sf_user',
        'password' => 'P@$$w0rdSF'
    ]
);

Connections::configure(
    'Sti',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=Sti;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);

Connections::configure(
    'Nsc',
    [
        'dsn'      => "pgsql:host=172.16.4.8;port=5432;dbname=Nsc;options='--client_encoding=UTF8'",
        'user'     => 'postgres',
        'password' => '1qaz2WSX$$'
    ]
);
?>
