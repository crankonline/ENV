<?php
namespace Environment\DataLayers\Requisites\Meta;
class Bank  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

	public function getBankById(string $id):array {
        $sql = <<<SQL
            SELECT * FROM "Common"."Bank" WHERE "IDBank" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $id ]);
        return $stmt->fetch();
    }

	public function getBank(string $id = null): array {
	    if(!is_null($id)) {
	        return $this->getBankById($id);
        }
		$sql = <<<SQL
            SELECT * FROM "Common"."Bank" ORDER BY "Name";
SQL;

		$stmt = $this->dbms->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function edit(string $bik, string $name, string $address, string $oldBik):void {

        $sql = <<<SQL
            UPDATE "Common"."Bank" SET
                "IDBank" = ?,
                "Name" = ?,
                "Address" = ?
            WHERE "IDBank" = ?;
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            $bik,
            $name,
            $address,
            $oldBik
        ]);
    }

    public function add(string $bik, string $name, string $address):string {
        $sql = <<<SQL
            INSERT INTO "Common"."Bank" ("IDBank", "Name", "Address")
            VALUES (?, ?, ?)
            RETURNING "IDBank"
SQL;

        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $bik, $name, $address ]);
        return $stmt->fetchColumn();
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

	public function addBank($id, $name, $address){
		$row['id'] = $id;
		$row['nameb'] = $name;
		$row['address'] = $address;

		$sql = <<<SQL
INSERT INTO     "Common"."Bank" 
(	"IDBank",     "Name",     "Address")
VALUES (:id, :nameb, :address);
    
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);

		//return $stmt->fetchColumn();
	}
}