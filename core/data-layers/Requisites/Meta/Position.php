<?php
namespace Environment\DataLayers\Requisites\Meta;

use PDO;

class Position  extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Requisites';

    /**
     * Position constructor.
     * @param PDO|null $dbms
     */
    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    /**
     * @return array
     */
    public function getPositions():array {
        $sql = <<<SQL
            SELECT * FROM "Common"."RepresentativePosition" ORDER BY "Name";
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id):array {
        $sql = <<<SQL
            SELECT * FROM "Common"."RepresentativePosition" WHERE "IDRepresentativePosition" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $id ]);
        return $stmt->fetch();
    }

    /**
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public function edit(int $id, string $name) {
        $sql = <<<SQL
            UPDATE "Common"."RepresentativePosition" SET "Name" = ? WHERE "IDRepresentativePosition" = ?;
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $name, $id ]);
        return $stmt->fetch();
    }

    /**
     * @param string $name
     * @return int
     */
    public function add(string $name):int {
        $sql = <<<SQL
            INSERT INTO "Common"."RepresentativePosition" (
                "IDRepresentativePosition",
                "Name"
            ) VALUES (
                (
                    SELECT MAX("IDRepresentativePosition") + 1 FROM "Common"."RepresentativePosition"
                ),
                ?
            ) RETURNING "IDRepresentativePosition";
SQL;
        $stmt = $this->dbms->prepare($sql);
        $stmt->execute([ $name ]);
        return $stmt->fetchColumn();
    }

}
