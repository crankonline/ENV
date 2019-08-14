<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 23.07.19
 * Time: 14:10
 */
require 'core/configuration.php';

use Unikum\Core\Dbms\ConnectionManager as Connections;


class migrate2 {


	function updateModuleGroup( $idModule, $moduleGroup,  $newIdModuleGroup ) {
		$sql  = <<<SQL
		UPDATE
    "Core"."Module"
SET
    "ModuleGroupID" = :newModuleGroup
WHERE
    ("ModuleGroupID" = :moduleGroupId) 
AND
	("IDModule" = :idModule) 
    ;

SQL;
		$stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

		return $stmt->execute( [
			'newModuleGroup'    => $newIdModuleGroup,
			'moduleGroupId' => $moduleGroup,
			'idModule' => $idModule
		] );
	}





}

$migrate = new migrate2();

$t = $migrate->updateModuleGroup("42","8","2");

if(isset($t)) {
	print_r( "migrate succes - " . $t );
}



