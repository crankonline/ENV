<?php
namespace Environment\DataLayers\Requisites\Meta;
class LegalForm  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array {
        $sql = <<<SQL
            SELECT * FROM "Common"."LegalForm" WHERE "IDLegalForm" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $id ]);
        return $stmt->fetch();
    }

	public function getLegalForm(){

		$values = [];
		$params = "";

		$sql = <<<SQL
SELECT
    *
FROM
    "Common"."LegalForm" as "c-lf"
        
{$params}
ORDER BY
    "c-lf"."Name";
SQL;

		$stmt = $this->dbms->prepare($sql);

		$stmt->execute($values);

		return $stmt->fetchAll();
	}

	public function edit(int $id, string $name, string $shortName, int $facet, int $ownershipFormID):void {
        $sql = <<<SQL
            UPDATE "Common"."LegalForm" SET
                "Name" = ?,
                "ShortName" = ?,
                "Facet" = ?,
                "OwnershipFormID" = ?
            WHERE "IDLegalForm" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([
            $name, $shortName, $facet, $ownershipFormID, $id
        ]);
    }

    public function add(string $name, string $shortName, int $facet, int $ownershipFormID):int {
        $sql = <<<SQL
            INSERT INTO "Common"."LegalForm" ( "IDLegalForm", "Name", "ShortName", "Facet", "OwnershipFormID" )
            VALUES (
                (
                    SELECT MAX("IDLegalForm") + 1 FROM "Common"."LegalForm"
                ),
                ?,
                ?,
                ?,
                ?
            )
            RETURNING "IDLegalForm"
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $name, $shortName, $facet, $ownershipFormID ]);
        return (int)$stmt->fetchColumn();
    }

	public function modifyLegalForm($id, $name, $shortName){
		$row['id'] = $id;
		$row['name'] = $name;
		$row['shortName'] = $shortName;

		$sql = <<<SQL
UPDATE
    "Common"."LegalForm" 
SET
    "Name" = :name,
    "ShortName" = :shortName
WHERE
    ("IDLegalForm" = :id);
SQL;

		$stmt = $this->dbms->prepare($sql);

		return $stmt->execute($row);
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