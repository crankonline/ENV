<?php

require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate15 {




    public function insertReiuisitesIdField( ) {
        $sql = <<<SQL
alter table "Statistics"."Action"
    add "RequisitesID" integer null;
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute();

        return $stmt->fetchColumn();
    }
    public function insertUserIdField( ) {
        $sql = <<<SQL
alter table "Statistics"."Action"
    add "UserID" integer null;
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute();

        return $stmt->fetchColumn();
    }


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
            'accesKey' => "client-registration-statistics-detail",
            'handleClass' => "ClientRegistrationStatisticsDetail",
            'namemg' => "Cтатистика регистрации клиентов детальная",
            'isEntryPoint' => 0
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

$migrate = new migrate15();
$m1 = $migrate->insertReiuisitesIdField();
$m2 = $migrate->insertUserIdField();

print_r( "migrate success - \n" . $m1 ."\n");
print_r( "migrate success - \n" . $m2 ."\n");


$moduleGroup = 2;
$m = $migrate->insertModule($moduleGroup);
$ma = $migrate->insertModuleAccess($m);

print_r( "migrate success - \n");
print_r($m."\n");
print_r($ma);



