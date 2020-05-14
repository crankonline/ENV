<?php


require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;

class migrate7 {

    public function insertModuleAccess( $id ) {
        $sql = <<<SQL
INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        :id,
        'can-send-tunduk-requisites',
        'Может отправлять компании в тундук'
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


$migrate = new migrate7();

$moduleGroup = 2; //Клиенты
$moduleId = 9; //Requistes
$access = $migrate->insertModuleAccess($moduleId);

//print_r(  $moduleId ."\n");
print_r(  $access ."\n");
