<?php
namespace Environment\DataLayers\Environment\Core;

class Visits extends \Environment\Core\DataLayer {
    public function register(array $row){
        $row = $this->toParams(
            $row,
            [
                'user-id'    => 'userId',
                'ip-address' => 'ipAddress'
            ]
        );

        $sql = <<<SQL
INSERT INTO "Core"."UserVisit"
    ("IDUserVisit", "UserID", "IpAddress", "DateTime")
VALUES
    (DEFAULT, :userId, :ipAddress, DEFAULT)
RETURNING
    "IDUserVisit";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function getByUser($userId, $limit = null){
        $sql = <<<SQL
SELECT
    "c-cv"."IDUserVisit" as "id",
    "c-cv"."IpAddress" as "ip-address",
    TO_CHAR("c-cv"."DateTime", 'DD.MM.YYYY HH24:MI:SS') as "date-time"
FROM
    "Core"."UserVisit" as "c-cv"
WHERE
    ("c-cv"."UserID" = :userId)
ORDER BY
    "c-cv"."DateTime" DESC
SQL;

        if($limit !== null){
            $sql .= ' LIMIT ' . abs((int)$limit);
        }

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'userId' => $userId
        ]);

        return $stmt->fetchAll();
    }
}
?>