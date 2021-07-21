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

	public function getChiefBasis(){

		$values = [];
		$params = "";

		$sql = <<<SQL
SELECT
    *
FROM
    "Common"."ChiefBasis.php" as "c-cb"
        
{$params}
ORDER BY
    "c-cb"."Name";
SQL;

		$stmt = $this->dbms->prepare($sql);

		$stmt->execute($values);

		return $stmt->fetchAll();
	}

	public function modifyChiefBasis($id, $name){
		$row['id'] = $id;
		$row['name'] = $name;

		$sql = <<<SQL
UPDATE
    "Common"."ChiefBasis.php"
SET
	"Name" = :name
WHERE
    ("IDChiefBasis" = :id);
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);
	}
}