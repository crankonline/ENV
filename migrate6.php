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
			'accesKey' => "sochi-reporting-forms",
			'handleClass' => "SochiReportingForms",
			'namemg' => "Открытие и закрытие форм отчетности",
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
            'accesKey' => "sochi-edit-period-reporting",
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
            'accesKey' => "sochi-zero-report-admin",
            'handleClass' => "SochiZeroReport",
            'namemg' => "Отправка нулевых отчетов",
            'isEntryPoint' => true
        ] );

        return $stmt->fetchColumn();
    }

    public function insertModuleSochiEditStiReport( $moduleGroup ) {
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
            'accesKey' => "sochi-edit-sti-report",
            'handleClass' => "SochiEditStiReport",
            'namemg' => "Редактирование отчетов Sti кураторского приложения",
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
    )RETURNING
    "IDModulePermission";


SQL;

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'id' => $id,
        ] );

        return $stmt->fetchColumn();
    }

}

$migrate = new migrate6();

$mg = 11; //Sochi
//$mg = $migrate->insertModuleGroupSochi();
//$m = $migrate->insertModuleSochiReportingForms($mg);
//$m2 = $migrate->insertModuleSochiEditPeriodReporting($mg);
//$m3 = $migrate->insertModuleSochiZeroReport($mg);
$m4 = $migrate->insertModuleSochiEditStiReport($mg);
//$ma = $migrate->insertModuleAccess($m);
//$ma2 = $migrate->insertModuleAccess($m2);
//$ma3 = $migrate->insertModuleAccess($m3);
$ma4 = $migrate->insertModuleAccess($m4);



//print_r( "migrate succes - \n" . $mg ."\n");
//print_r($m.":".$m2.":".$m3.":".$m4."\n");
//print_r($ma. ":". $ma2. ":".$ma3.":".$ma4);
print_r( "migrate succes - \n" . $mg ."\n");
print_r($m4."\n");
print_r($ma4);




