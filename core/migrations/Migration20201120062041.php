<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate6
 * Class Migration20201120062041
 * @package Requisites\Migrations
 */
class Migration20201120062041 extends Migration {

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
        'Сочи'
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
            'accesKey' => "sochi-reporting-forms",
            'handleClass' => "SochiReportingForms",
            'namemg' => "Открытие и закрытие форм отчетности",
            'isEntryPoint' => true
        ] );

        $id1 = $stmt->fetchColumn();




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
            'accesKey' => "sochi-edit-period-reporting",
            'handleClass' => "SochiEditPeriodReporting",
            'namemg' => "Редактирование периодов сдачи отчетов",
            'isEntryPoint' => true
        ] );

        $id2 = $stmt->fetchColumn();




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
            'accesKey' => "sochi-zero-report-admin",
            'handleClass' => "SochiZeroReport",
            'namemg' => "Отправка нулевых отчетов",
            'isEntryPoint' => true
        ] );

        $id3 = $stmt->fetchColumn();



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
            'accesKey' => "sochi-edit-sti-report",
            'handleClass' => "SochiEditStiReport",
            'namemg' => "Редактирование отчетов Sti кураторского приложения",
            'isEntryPoint' => true
        ] );

        $id4 = $stmt->fetchColumn();


        self::insertModuleAccess($id1,$dbms);
        self::insertModuleAccess($id2,$dbms);
        self::insertModuleAccess($id3,$dbms);
        self::insertModuleAccess($id4,$dbms);
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {
        $sql = <<<SQL
            SELECT m."IDModuleGroup" FROM "Core"."ModuleGroup" as m
            WHERE m."Name" = 'Сочи';
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $moduleGroup = $stmt->fetchColumn();



        $id1 = self::selectModuleId($dbms, [
                'moduleGroupId' => $moduleGroup,
                'accesKey' => "sochi-reporting-forms",
                'handleClass' => "SochiReportingForms",
                'namemg' => "Открытие и закрытие форм отчетности",
                'isEntryPoint' => true
            ] );
        $id2 = self::selectModuleId($dbms, [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "sochi-edit-period-reporting",
            'handleClass' => "SochiEditPeriodReporting",
            'namemg' => "Редактирование периодов сдачи отчетов",
            'isEntryPoint' => true
        ] );
        $id3 = self::selectModuleId($dbms, [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "sochi-zero-report-admin",
            'handleClass' => "SochiZeroReport",
            'namemg' => "Отправка нулевых отчетов",
            'isEntryPoint' => true
        ] );
        $id4 = self::selectModuleId($dbms, [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "sochi-edit-sti-report",
            'handleClass' => "SochiEditStiReport",
            'namemg' => "Редактирование отчетов Sti кураторского приложения",
            'isEntryPoint' => true
        ] );

        self::deleteModulePermission($id1, $dbms);
        self::deleteModulePermission($id2, $dbms);
        self::deleteModulePermission($id3, $dbms);
        self::deleteModulePermission($id4, $dbms);

        self::deleteModule($id1, $dbms);
        self::deleteModule($id2, $dbms);
        self::deleteModule($id3, $dbms);
        self::deleteModule($id4, $dbms);


        $sql = <<<SQL
            DELETE FROM "Core"."ModuleGroup" 
            WHERE "IDModuleGroup" = :idModuleRoup;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'idModuleRoup' => $moduleGroup,
        ] );
	}


    function insertModuleAccess( int $id , PDO $dbms ) {
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

        return $stmt->fetchColumn();
    }

    function deleteModulePermission( int $id, PDO $dbms ) {
        $sql = <<<SQL
            DELETE FROM "Core"."Module" 
            WHERE "IDModule" = :id;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute( [
            'id' => $id,
        ] );
    }

    function selectModuleId ( PDO $dbms, array $arr) {

        $sql = <<<SQL
            SELECT m."IDModule" FROM "Core"."Module" as m
            WHERE m."ModuleGroupID" = :moduleGroupId
            AND m."AccessKey" = :accesKey
            AND m."HandlerClass" = :handleClass
            AND m."Name" = :namemg
            AND m."IsEntryPoint" = :isEntryPoint;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute($arr);
        $id = $stmt->fetchColumn();
        return $id;

    }

    function deleteModule(int $id, PDO $dbms) {
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
