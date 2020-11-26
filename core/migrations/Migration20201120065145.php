<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate9
 * Class Migration20201120065145
 * @package Requisites\Migrations
 */
class Migration20201120065145 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {

        $sql = <<<SQL
INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (
        DEFAULT,
        'Платежи'
    )RETURNING
    "IDModuleGroup";


SQL;

        $stmt = $dbms->prepare( $sql );

        $stmt->execute();

        $moduleGroup = $stmt->fetchColumn();





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
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "log-terminal",
            'handleClass' => "LogTerminal",
            'namemg' => "Логи платежей",
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
    )RETURNING
    "IDModulePermission";


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
            SELECT m."IDModuleGroup" FROM "Core"."ModuleGroup" as m
            WHERE m."Name" = 'Платежи';
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $moduleGroup = $stmt->fetchColumn();



        $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
            WHERE m."ModuleGroupID" = :moduleGroupId
            AND m."AccessKey" = :accesKey
            AND m."HandlerClass" = :handleClass
            AND m."Name" = :namemg
            AND m."IsEntryPoint" = :isEntryPoint;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute( [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "log-terminal",
            'handleClass' => "LogTerminal",
            'namemg' => "Логи платежей",
            'isEntryPoint' => true
        ] );
        $id = $stmt->fetchColumn();





        $sql = <<<SQL
            DELETE FROM "Core"."Module" 
            WHERE "IDModule" = :id;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'id' => $id,
        ] );






        $sql = <<<SQL
            DELETE FROM "Core"."ModuleGroup" 
            WHERE "IDModuleGroup" = :idModuleRoup;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'idModuleRoup' => $moduleGroup,
        ] );
    }

}
