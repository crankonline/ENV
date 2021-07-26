<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 19.08.19
 * Time: 16:23
 */

namespace Environment\DataLayers\Requisites\Meta;


class ChiefBasis  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

	public function getChiefBasis():array {
		$sql = <<<SQL
            SELECT * FROM "Common"."ChiefBasis" ORDER BY "Name";
SQL;
		$stmt = $this->dbms->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getById(int $id):array {
        $sql = <<<SQL
            SELECT * FROM "Common"."ChiefBasis" WHERE "IDChiefBasis" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $id ]);
        return $stmt->fetch();
    }

    public function edit(int $id, string $name) {
	    $sql = <<<SQL
            UPDATE "Common"."ChiefBasis" SET "Name" = ? WHERE "IDChiefBasis" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $name, $id ]);
        return $stmt->fetch();
    }

    public function add(string $name):int {
	    $sql = <<<SQL
            INSERT INTO "Common"."ChiefBasis" (
                "IDChiefBasis",
                "Name"
            ) VALUES (
                (
                    SELECT MAX("IDChiefBasis") + 1 FROM "Common"."ChiefBasis"
                ),
                ?
            ) RETURNING "IDChiefBasis";
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $name ]);
        return $stmt->fetchColumn();
    }

	public function modifyChiefBasis($id, $name){
		$row['id'] = $id;
		$row['name'] = $name;

		$sql = <<<SQL
UPDATE
    "Common"."ChiefBasis"
SET
	"Name" = :name
WHERE
    ("IDChiefBasis" = :id);
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);
	}
}