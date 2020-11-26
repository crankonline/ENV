<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate10
 * Class Migration20201120065420
 * @package Requisites\Migrations
 */
class Migration20201120065420 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $moduleGroup = 8;

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
            'accesKey' => "pdf-delivery-period",
            'handleClass' => "PDFDeliveryPeriod",
            'namemg' => "PDF период сдачи",
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

        $stmt->fetchColumn();
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

        $moduleGroup = 8;

        $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
          WHERE m."ModuleGroupID" = :moduleGroupId
            AND m."AccessKey" = :accesKey
            AND m."HandlerClass" = :handleClass
            AND m."Name" = :namemg
            AND m."IsEntryPoint" = :isEntryPoint;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute(
            [            'moduleGroupId' => $moduleGroup,
                'accesKey' => "pdf-delivery-period",
                'handleClass' => "PDFDeliveryPeriod",
                'namemg' => "PDF период сдачи",
                'isEntryPoint' => true
            ]
        );
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
