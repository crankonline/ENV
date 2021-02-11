<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate15
 * Class Migration20201120070846
 * @package Requisites\Migrations
 */
class Migration20201120070846 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
alter table "Statistics"."Action"
    add "RequisitesID" integer null;
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute();

        $stmt->fetchColumn();

        $sql = <<<SQL
alter table "Statistics"."Action"
    add "UserID" integer null;
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute();

        $stmt->fetchColumn();








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
            'moduleGroupId' => null,
            'accesKey' => "client-registration-statistics-detail",
            'handleClass' => "ClientRegistrationStatisticsDetail",
            'namemg' => "Cтатистика регистрации клиентов детальная",
            'isEntryPoint' => 0
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

        $stmt->fetchColumn();
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

	}

}
