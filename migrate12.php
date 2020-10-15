<?php

require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate12 {

    public function insertModule() {
        $sql = <<<SQL
INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        :moduleGroupId,
        :accesKey,
        :handleClass,
        :namemg,
        :isEntryPoint
    )RETURNING
    "IDModule";


SQL;

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'moduleGroupId' => null,
            'accesKey' => "diff-requisites",
            'handleClass' => "DiffRequisites",
            'namemg' => "сравнение реквизитов",
            'isEntryPoint' => true
        ] );

        return $stmt->fetchColumn();
    }

    public function insertModuleAccess( $id ) {
        $sql = <<<SQL
INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        :id,
        'can-access',
        'Доступ к модулю'
    )RETURNING
    "IDModulePermission";


SQL;

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'id' => $id,
        ] );

        return $stmt->fetchColumn();
    }

}

$migrate = new migrate11();


$m = $migrate->insertModule();
$ma = $migrate->insertModuleAccess($m);

print_r( "migrate success - \n");
print_r($m."\n");
print_r($ma);




