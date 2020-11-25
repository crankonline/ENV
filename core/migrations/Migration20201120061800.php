<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate4
 * Class Migration20201120061800
 * @package Requisites\Migrations
 */
class Migration20201120061800 extends Migration {

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
        :moduleGroupId,
        :accesKey,
        :handleClass,
        :namemg,
        :isEntryPoint
    )RETURNING
    "IDModule";


SQL;

        $stmt = $dbms->prepare( $sql );

        $stmt->execute( [
            'moduleGroupId' => 8,
            'accesKey' => "sf-archive",
            'handleClass' => "SfArchive",
            'namemg' => "Архив соцфонда",
            'isEntryPoint' => true
        ] );

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

//        return $stmt->fetchColumn();
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

        $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
            WHERE m."ModuleGroupID" = 8
            AND m."AccessKey" = 'sf-archive'
            AND m."HandlerClass" = 'SfArchive'
            AND m."Name" = 'Архив соцфонда'
            AND m."IsEntryPoint" = false;
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
