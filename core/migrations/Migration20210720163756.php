<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

class Migration20210720163756 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
            INSERT INTO "Core"."ModuleGroup" ("Name") VALUES (?) RETURNING "IDModuleGroup";
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ 'Мета-справочники реквизитов' ]);
        $idModuleGroup = $stmt->fetchColumn();

        $sql = <<<SQL
            INSERT INTO "Core"."Module" ("ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
            VALUES (?, ?, ?, ?, ?)
            RETURNING "IDModule";
SQL;
        $values = [
            [ $idModuleGroup, "meta-legal-form", "RequisitesMeta\\LegalForm", "Организационно-правовая форма", true ],
            [ $idModuleGroup, "meta-bank", "RequisitesMeta\\Bank", "Банки", true ],
            [ $idModuleGroup, "meta-gked", "RequisitesMeta\\Gked", "ГКЭД", true ],
            [ $idModuleGroup, "meta-chief-basis", "RequisitesMeta\\ChiefBasis.php", "Основание на занимаемой должности", true ]
        ];

        $modules = [];

        foreach ($values as $value) {
            $stmt = $dbms->prepare($sql);
            $stmt->execute($value);
            $modules[] = $stmt->fetchColumn();
        }

        $sql = <<<SQL
            INSERT INTO "Core"."ModulePermission" ("ModuleID", "Mark", "Name")
            VALUES (?, ?, ?)
            RETURNING "IDModulePermission"; 	
SQL;
        $permissions = [];
        foreach ($modules as $module) {
            $stmt = $dbms->prepare($sql);
            $stmt->execute([ $module, 'can-access', 'Доступ к модулю' ]);
            $permissions[] = $stmt->fetchColumn();
        }

        $sql = <<<SQL
            INSERT INTO "Core"."UserRoleModulePermission" ("UserRoleID", "ModulePermissionID")
            VALUES (?, ?);
SQL;

        foreach ($permissions as $permission) {
            $stmt = $dbms->prepare($sql);
            $stmt->execute([ 7, $permission ]);
            $stmt->execute([ 1, $permission ]);
        }
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

        $sql = <<<SQL
            SELECT "IDModuleGroup" FROM "Core"."ModuleGroup" WHERE "Name" = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ 'Мета-справочники реквизитов' ]);
        $idModuleGroup = $stmt->fetchColumn();


        $sql = <<<SQL
            DELETE FROM "Core"."Module" WHERE "ModuleGroupID" = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ $idModuleGroup ]);

        $sql = <<<SQL
            DELETE FROM "Core"."ModuleGroup" WHERE "IDModuleGroup"  = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([ $idModuleGroup ]);

    }

}
