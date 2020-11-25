<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate7
 * Class Migration20201120064626
 * @package Requisites\Migrations
 */
class Migration20201120064626 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {

        $moduleId = 9; //Requistes

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

        $stmt = $dbms->prepare( $sql );

        $stmt->execute( [
            'id' => $moduleId,
        ] );

//        return $stmt->fetchColumn();
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {


        $sql = <<<SQL
            DELETE FROM "Core"."ModulePermission"
            WHERE "ModuleID" = :id
            AND "Mark" = :mark
            AND "Name" = :nam;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'id' => 9,
            'mark' => "can-send-tunduk-requisites",
            'nam' => "Может отправлять компании в тундук"
        ] );


    }

}
