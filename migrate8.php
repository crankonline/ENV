<?php

require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate8 {

    public function insertModuleSochiEditStiReport( $moduleGroup ) {
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
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "media-server",
            'handleClass' => "MediaServer",
            'namemg' => "MediaServer",
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

$migrate = new migrate8();

$mg = 9;
$m4 = $migrate->insertModuleSochiEditStiReport($mg);
$ma4 = $migrate->insertModuleAccess($m4);

print_r( "migrate success - \n" . $mg ."\n");
print_r($m4."\n");
print_r($ma4);




