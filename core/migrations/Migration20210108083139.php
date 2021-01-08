<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

class Migration20210108083139 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {

		$sql = <<<SQL
			INSERT INTO "Core"."Module"(
					"ModuleGroupID",
					"AccessKey",
					"HandlerClass",
					"Name",
					"IsEntryPoint"
				) VALUES (
					(
						SELECT "IDModuleGroup" FROM "Core"."ModuleGroup"
						WHERE "Name" = 'Сочи' LIMIT 1
					),
					'sochi-exporters',
					'SochiExporters',
					'Управление экспортерами',
					't'
				)
				RETURNING "IDModule";
SQL;
		$stmt = $dbms->prepare($sql);
		$stmt->execute();
		$idModule = $stmt->fetch()['IDModule'];

		$sql = <<<SQL
			INSERT INTO "Core"."ModulePermission" ("ModuleID", "Mark", "Name")
			VALUES (?, 'can-access', 'Доступ к модулю')
			RETURNING "IDModulePermission";
SQL;
		$stmt = $dbms->prepare($sql);
		$stmt->execute([ $idModule ]);
		$idModulePermission = $stmt->fetch()['IDModulePermission'];

		$permissions = [
			'Разработчик ПО',
			$idModulePermission,
			'Cудо', 
			$idModulePermission,
			'Администратор', 
			$idModulePermission
		];

		$sql = <<<SQL
			INSERT INTO "Core"."UserRoleModulePermission" (
				"UserRoleID",
				"ModulePermissionID"
			)
			VALUES 
			(
				(
					SELECT "IDUserRole" FROM "Core"."UserRole" WHERE "Name" = ?
				),
				?
			),
			(
				(
					SELECT "IDUserRole" FROM "Core"."UserRole" WHERE "Name" = ?
				),
				?
			),
			(
				(
					SELECT "IDUserRole" FROM "Core"."UserRole" WHERE "Name" = ?
				),
				?
			);
SQL;
		$stmt = $dbms->prepare($sql);
		$stmt->execute($permissions);

	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

		$sql = <<<SQL
			DELETE FROM "Core"."Module" WHERE "AccessKey" = 'sochi-exporters';
SQL;
		$stmt = $dbms->prepare();
		$stmt->execute();

	}

}
