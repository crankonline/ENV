<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 19.08.19
 * Time: 16:23
 */

namespace Environment\DataLayers\Requisites\Meta;


class Activity  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

    public function getById(int $id):array {
        $sql = <<<SQL
SELECT * FROM "Common"."Activity" WHERE "IDActivity" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $id ]);
        return $stmt->fetch();
    }

	public function getActivity():array{
		$sql = <<<SQL
            SELECT * FROM "Common"."Activity" ORDER BY "Gked";
SQL;
		$stmt = $this->dbms->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function edit(int $id, string $gked, string $name, string $parentGked) {
	    $sql = <<<SQL
            UPDATE "Common"."Activity" SET
                "ActivityID" = (
                    SELECT "IDActivity" FROM "Common"."Activity" WHERE "Gked" = ?
                ),
                "Name" = ?,
                "Gked" = ?
            WHERE "IDActivity" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $parentGked, $name, $gked, $id ]);
    }

    public function add(string $gked, string $name, string $parentGked):int {
        $sql = <<<SQL
            INSERT INTO "Common"."Activity" ("IDActivity", "Gked", "Name", "ActivityID")
            VALUES (
                (
                    SELECT  MAX("IDActivity") + 1 FROM "Common"."Activity"
                ),
                ?,
                ?,
                (
                    SELECT "IDActivity" FROM "Common"."Activity" WHERE "Gked" = ?
                )
            )
            RETURNING "IDActivity";
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $gked, $name, $parentGked ]);
        return $stmt->fetchColumn();
    }

	public function modifyActivity($id, $activityId, $name, $gked){
		$row['id'] = $id;
		$row['activityId'] = $activityId;
		$row['name'] = $name;
		$row['gked'] = $gked;

		$sql = <<<SQL
UPDATE
    "Common"."Activity"
SET
	"ActivityID" = :activityId,
    "Name" = :name,
    "Gked" = :gked
WHERE
    ("IDActivity" = :id);
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);
	}
}