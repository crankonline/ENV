<?php
namespace Environment\Migrations;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Unikum\Core\Migration;

class Migration20210813170537 extends Migration {

	/**
	 * @param PDO $dbms
	 */
	protected static function up(PDO $dbms):void {
        $sql = <<<SQL
            UPDATE "Core"."Module" SET "HandlerClass" = ? WHERE "AccessKey" = ?;
SQL;
        $stmt = $dbms->prepare($sql);
        $stmt->execute([
            "RequisitesMeta\\ChiefBasis", "meta-chief-basis"
        ]);

	}

	/**
	 * @param PDO $dbms
	 */
	protected static function down(PDO $dbms):void {

	}

}
