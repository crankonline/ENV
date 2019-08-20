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

	public function getActivity(){

		$values = [];
		$params = "";

		$sql = <<<SQL
SELECT
    *
FROM
    "Common"."Activity" as "c-a"
        
{$params}
ORDER BY
    "c-a"."Name";
SQL;

		$stmt = $this->dbms->prepare($sql);

		$stmt->execute($values);

		return $stmt->fetchAll();
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