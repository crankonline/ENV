<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

class Migration20210814112445 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
            SELECT "IDModuleGroup" FROM "Core"."ModuleGroup" WHERE "Name" = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ 'Мета-справочники реквизитов' ]);
        $idModuleGroup = $stmt->fetchColumn();

        $sql = <<<SQL
            INSERT INTO "Core"."Module" ("ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
            VALUES (?, ?, ?, ?, ?)
            RETURNING "IDModule";
SQL;

        $stmt = $dbms->prepare($sql);
        $stmt->execute([
            $idModuleGroup,
            "meta-position",
            "RequisitesMeta\\Position",
            "Должности",
            true
        ]);

        $idModule = $stmt->fetchColumn();

        $sql = <<<SQL
            INSERT INTO "Core"."ModulePermission" ("ModuleID", "Mark", "Name")
            VALUES (?, ?, ?)
            RETURNING "IDModulePermission"; 	
SQL;

        $stmt = $dbms->prepare($sql);
        $stmt->execute([ $idModule, 'can-access', 'Доступ к модулю' ]);
        $idPermission = $stmt->fetchColumn();

        $sql = <<<SQL
            INSERT INTO "Core"."UserRoleModulePermission" ("UserRoleID", "ModulePermissionID")
            VALUES (?, ?);
SQL;

        $stmt = $dbms->prepare($sql);
        $stmt->execute([ 7, $idPermission ]);
        $stmt->execute([ 1, $idPermission ]);
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {
        $sql = <<<SQL
            DELETE FROM "Core"."Module" WHERE "AccessKey" = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ 'meta-position' ]);
	}

}
