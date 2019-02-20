<?php
namespace Environment\DataLayers\Environment\Core;

class UserRoles extends \Environment\Core\DataLayer {
    public function getById($id){
        $sql = <<<SQL
SELECT
    "c-ur"."IDUserRole" as "id",
    "c-ur"."Name" as "name",
    COUNT("c-u".*) as "users-count"
FROM
    "Core"."UserRole" as "c-ur"
        LEFT JOIN "Core"."User" as "c-u"
            ON "c-ur"."IDUserRole" = "c-u"."UserRoleID"
WHERE
    ("c-ur"."IDUserRole" = :id)
GROUP BY
    1;
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    public function getAll(){
        $sql = <<<SQL
SELECT
    "c-ur"."IDUserRole" as "id",
    "c-ur"."Name" as "name",
    COUNT("c-u".*) as "users-count"
FROM
    "Core"."UserRole" as "c-ur"
        LEFT JOIN "Core"."User" as "c-u"
            ON "c-ur"."IDUserRole" = "c-u"."UserRoleID"
GROUP BY
    1
ORDER BY
    "c-ur"."Name";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function register(array $row){
        $row = $this->toParams(
            $row,
            [
                'name' => null
            ]
        );

        $sql = <<<SQL
INSERT INTO "Core"."UserRole"
    ("IDUserRole", "Name")
VALUES
    (DEFAULT, :name)
RETURNING
    "IDUserRole";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function modify($id, array $row){
        $row = $this->toParams(
            $row,
            [
                'name' => null
            ]
        );

        $row['id'] = $id;

        $sql = <<<SQL
UPDATE
    "Core"."UserRole"
SET
    "Name" = :name
WHERE
    ("IDUserRole" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute($row);
    }

    public function remove($id){
        $sql = <<<SQL
DELETE FROM
    "Core"."UserRole"
WHERE
    ("IDUserRole" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }
}
?>
