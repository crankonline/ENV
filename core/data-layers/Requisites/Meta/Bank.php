<?php
namespace Environment\DataLayers\Requisites\Meta;
class Bank  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

	public function getBank(){

		$values = [];
		$params = "";

		$sql = <<<SQL
SELECT
    *
FROM
    "Common"."Bank" as "c-lf"
        
{$params}
ORDER BY
    "c-lf"."Name";
SQL;

		$stmt = $this->dbms->prepare($sql);

		$stmt->execute($values);

		return $stmt->fetchAll();
	}

	public function modifyBank($id, $name, $address){
		$row['id'] = $id;
		$row['name'] = $name;
		$row['address'] = $address;

		$sql = <<<SQL
UPDATE
    "Common"."Bank" 
SET
	"IDBank" = :id,
    "Name" = :name,
    "Address" = :address
WHERE
    ("IDBank" = :id);
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);
	}
}