<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 21.10.19
 * Time: 14:10
 */
require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate5 {


	public function findModule( ) {
		$sql = <<<SQL
Select "IDModule" from "Core"."Module" where "AccessKey" = 'country-passports';
SQL;
		$stmt = Connections::getConnection( 'Environment' )->prepare( $sql );
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	public function dropModule ( $id ) {
        $sql = <<<SQL
DELETE FROM "Core"."Module" WHERE "IDModule" = :id;
SQL;
        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );
        $stmt->execute(["id"=>$id]);
    }

//    public function findModulePermission( $id ) {
//        $sql = <<<SQL
//Select "IDModulePermission" from "Core"."ModulePermission" where "ModuleID" = :id;
//SQL;
//        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );
//        $stmt->execute(['id'=>$id]);
//        return $stmt->fetchColumn();
//    }
//
//    public function dropModulePermission ( $id ) {
//        $sql = <<<SQL
//DELETE FROM "Core"."Module" WHERE "IDModule" = :id;
//SQL;
//        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );
//        $stmt->execute(["id"=>$id]);
//    }
//
//    public function dropUserRoleModulePermission ( $id ) {
//        $sql = <<<SQL
//DELETE FROM "Core"."UserRoleModulePermission" WHERE "ModulePermissionID" = :id;
//SQL;
//        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );
//        $stmt->execute(["id"=>$id]);
//    }

}

$migrate = new migrate5();

$module = $migrate->findModule();
if(isset($module)) {
    echo ( "module id - " . $module . " \r\n");
}
$migrate->dropModule($module);
echo ("dropped module \r\n");
//$modulePermission = $migrate->findModulePermission($module);
//if(isset($modulePermission)) {
//    echo("module permission id - " . $modulePermission . "\r\n");
//}
//$migrate->dropModulePermission($modulePermission);
//echo ("dropped ModulePermission");
//$migrate->dropUserRoleModulePermission($modulePermission);
//echo ("dropped UserRoleModulePermission");




