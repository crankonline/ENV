<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate2
 * Class Migration20201120061210
 * @package Requisites\Migrations
 */
class Migration20201120061210 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
//        function updateModuleGroup( $idModule, $moduleGroup,  $newIdModuleGroup ) {
	    //$t = $migrate->updateModuleGroup("42","8","2");

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





        $sql  = <<<SQL
		UPDATE
    "Core"."Module"
SET
    "ModuleGroupID" = :newModuleGroup
WHERE
    ("ModuleGroupID" = :moduleGroupId) 
AND
	("IDModule" = :idModule) 
    ;

SQL;
        $stmt = $dbms->prepare( $sql );
        $id = $stmt->execute( [
            'newModuleGroup'    => 2,
            'moduleGroupId' => 8,
            'idModule' => $id
        ] );
    }


	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {
        $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
            WHERE m."ModuleGroupID" = 2
            AND m."AccessKey" = 'reregister'
            AND m."HandlerClass" = 'Reregister'
            AND m."Name" = 'Перерегистрация клиентов'
            AND m."IsEntryPoint" = 1;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $id = $stmt->fetchColumn();





        $sql  = <<<SQL
		UPDATE
    "Core"."Module"
SET
    "ModuleGroupID" = :newModuleGroup
WHERE
    ("ModuleGroupID" = :moduleGroupId) 
AND
	("IDModule" = :idModule) 
    ;

SQL;
        $stmt = $dbms->prepare( $sql );
        $id = $stmt->execute( [
            'newModuleGroup'    => 8,
            'moduleGroupId' => 2,
            'idModule' => $id
        ] );
	}

}
