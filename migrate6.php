<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 23.07.19
 * Time: 14:10
 */
require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate6 {

    public function insertModuleGroupSochi() {
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

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute();

        return $stmt->fetchColumn();
    }

	public function insertModuleSochiReportingForms( $moduleGroup ) {
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

		$stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

		$stmt->execute( [
			'moduleGroupId' => $moduleGroup,
			'accesKey' => "reporting-forms",
			'handleClass' => "SochiReportingForms",
			'namemg' => "Окрытие и закрытие форм отчетности",
			'isEntryPoint' => true
		] );

		return $stmt->fetchColumn();
	}

    public function insertModuleSochiEditPeriodReporting( $moduleGroup ) {
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

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "edit-period-reporting",
            'handleClass' => "SochiEditPeriodReporting",
            'namemg' => "Редактирование периодов сдачи отчетов",
            'isEntryPoint' => true
        ] );

        return $stmt->fetchColumn();
    }

    public function insertModuleSochiZeroReport( $moduleGroup ) {
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

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'moduleGroupId' => $moduleGroup,
            'accesKey' => "zero-report-admin",
            'handleClass' => "SochiZeroReport",
            'namemg' => "Отправка нулевых отчетов",
            'isEntryPoint' => true
        ] );

        return $stmt->fetchColumn();
    }

    public function insertModuleAccess( $id ) {
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

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'id' => $id,
        ] );

        return $stmt->fetchColumn();
    }

}

$migrate = new migrate6();

$mg = $migrate->insertModuleGroupSochi();
$m = $migrate->insertModuleSochiReportingForms($mg);
$m2 = $migrate->insertModuleSochiEditPeriodReporting($mg);
$m3 = $migrate->insertModuleSochiZeroReport($mg);
$ma = $migrate->insertModuleAccess($m);
$ma2 = $migrate->insertModuleAccess($m2);
$ma3 = $migrate->insertModuleAccess($m3);



print_r( "migrate succes - \n" . $mg ."\n");
print_r($m.":".$m2.":".$m3."\n");
print_r($ma. ":". $ma2. ":".$ma3);




