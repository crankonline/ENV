<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 21.10.19
 * Time: 14:10
 */
require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate4 {


	public function insertModule( ) {
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
			'moduleGroupId' => 8,
			'accesKey' => "sf-archive",
			'handleClass' => "SfArchive",
			'namemg' => "Архив соцфонда",
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

$migrate = new migrate4();

$t = $migrate->insertModule();
$migrate->insertModuleAccess($t);

if(isset($t)) {
	print_r( "migrate succes - " . $t );
}



