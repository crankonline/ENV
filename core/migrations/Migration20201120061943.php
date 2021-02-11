<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

/**
 * migrate5
 * Class Migration20201120061943
 * @package Requisites\Migrations
 */
class Migration20201120061943 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
Select "IDModule" from "Core"."Module" where "AccessKey" = 'country-passports';
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute();
        $id = $stmt->fetchColumn();


        $sql = <<<SQL
DELETE FROM "Core"."Module" WHERE "IDModule" = :id;
SQL;
        $stmt = $dbms->prepare( $sql );
        $stmt->execute(["id"=>$id]);
	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {
        //7	country-passports	CountryPassports	Паспортные данные по стране	true
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
            'moduleGroupId' => 7,
            'accesKey' => "country-passports",
            'handleClass' => "CountryPassports",
            'namemg' => "Паспортные данные по стране",
            'isEntryPoint' => true
        ] );

        $id = $stmt->fetchColumn();
	}

}
