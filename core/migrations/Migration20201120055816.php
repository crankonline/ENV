<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate1
 * Class Migration20201120055816
 * @package Requisites\Migrations
 */
class Migration20201120055816 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
            INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        8,
        'reregister',
        'Reregister',
        'Перерегистрация клиентов',
        1
    )RETURNING
    "IDModule";

SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $id = $stmt->fetchColumn();




        $sql = <<<SQL
INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        :id,
        'can-access',
        'Доступ к модулю'
    );
    
SQL;
    
        $stmt = $dbms->prepare( $sql );

		$stmt->execute( [
			'id' => $id,
		] );





	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

	    $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
            WHERE m."ModuleGroupID" = 8
            AND m."AccessKey" = 'reregister'
            AND m."HandlerClass" = 'Reregister'
            AND m."Name" = 'Перерегистрация клиентов'
            AND m."IsEntryPoint" = 1;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $id = $stmt->fetchColumn();




        $sql = <<<SQL
            DELETE FROM "Core"."ModulePermission"
            WHERE "ModuleID" = :id;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'id' => $id,
        ] );




        $sql = <<<SQL
            DELETE FROM "Core"."Module" 
            WHERE "IDModule" = :id;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'id' => $id,
        ] );



    }

}
