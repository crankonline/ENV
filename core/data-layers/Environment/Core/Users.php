<?php
namespace Environment\DataLayers\Environment\Core;

class Users extends \Environment\Core\DataLayer {
    public function getById($id){
        $sql = <<<SQL
SELECT
    "c-u"."IDUser" as "id",
    "c-ur"."IDUserRole" as "user-role-id",
    "c-ur"."Name" as "user-role-name",
    "c-m"."IDModule" as "module-id",
    "c-m"."AccessKey" as "module-access-key",
    "c-m"."Name" as "module-name",
    "c-u"."Login" as "login",
    "c-u"."Surname" as "surname",
    "c-u"."Name" as "name",
    "c-u"."MiddleName" as "middle-name",
    "c-u"."IsActive" as "is-active",
    "c-u"."Phone" as "phone",
    "c-u"."IsPasswordExpired" as "is-password-expired"
FROM
    "Core"."User" as "c-u"
        INNER JOIN "Core"."UserRole" as "c-ur"
            ON "c-u"."UserRoleID" = "c-ur"."IDUserRole"
        LEFT JOIN "Core"."Module" as "c-m"
            ON "c-u"."ModuleID" = "c-m"."IDModule"
WHERE
    ("c-u"."IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    public function getBy(array $filters, $limit = null, $offset = null){
        $params = [];
        $limits = [];
        $values = [];

        if(array_key_exists('user-role-id', $filters)){
            $params[] = '("c-ur"."IDUserRole" = :userRoleId)';

            $values['userRoleId'] = $filters['user-role-id'];
        } elseif(array_key_exists('user-role-id-except', $filters)) {
            $params[] = '("c-ur"."IDUserRole" <> :userRoleId)';

            $values['userRoleId'] = $filters['user-role-id-except'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    COUNT(*)
FROM
    "Core"."User" as "c-u"
        INNER JOIN "Core"."UserRole" as "c-ur"
            ON "c-u"."UserRoleID" = "c-ur"."IDUserRole"
{$params};
SQL;
        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        $count = $stmt->fetchColumn();

        if($limit !== null){
            $limits[] = 'LIMIT :limit';

            $values['limit'] = $limit;

            if($offset !== null){
                $limits[] = 'OFFSET :offset';

                $values['offset'] = $offset;
            }
        }

        $limits = $limits ? implode(PHP_EOL, $limits) : '';

        $sql = <<<SQL
SELECT
    "c-u"."IDUser" as "id",
    "c-ur"."IDUserRole" as "user-role-id",
    "c-ur"."Name" as "user-role-name",
    "c-u"."Login" as "login",
    "c-u"."Surname" as "surname",
    "c-u"."Name" as "name",
    "c-u"."MiddleName" as "middle-name",
    "c-u"."IsActive" as "is-active"
FROM
    "Core"."User" as "c-u"
        INNER JOIN "Core"."UserRole" as "c-ur"
            ON "c-u"."UserRoleID" = "c-ur"."IDUserRole"
{$params}
ORDER BY
    "c-u"."IsActive" DESC,
    "c-u"."Surname",
    "c-u"."Name",
    "c-u"."MiddleName"
{$limits};
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        $rows = $stmt->fetchAll();

        return [ &$count, &$rows ];
    }

    public function authenticate($login, $password){
        $sql = <<<SQL
SELECT
    "c-u"."IDUser" as "id",
    "c-ur"."IDUserRole" as "user-role-id",
    "c-ur"."Name" as "user-role-name",
    "c-m"."IDModule" as "module-id",
    "c-m"."AccessKey" as "module-access-key",
    "c-m"."Name" as "module-name",
    "c-u"."Login" as "login",
    "c-u"."Surname" as "surname",
    "c-u"."Name" as "name",
    "c-u"."MiddleName" as "middle-name",
    "c-u"."IsActive" as "is-active",
    "c-u"."Phone" as "phone",
    "c-u"."IsPasswordExpired" as "is-password-expired"
FROM
    "Core"."User" as "c-u"
        INNER JOIN "Core"."UserRole" as "c-ur"
            ON "c-u"."UserRoleID" = "c-ur"."IDUserRole"
        LEFT JOIN "Core"."Module" as "c-m"
            ON "c-u"."ModuleID" = "c-m"."IDModule"
WHERE
    ("c-u"."Login" = :login)
    AND
    ("c-u"."Password" = DECODE(:password, 'hex'))
    AND
    "c-u"."IsActive";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'login'    => $login,
            'password' => hash('sha256', $password)
        ]);

        return $stmt->fetch();
    }

    public function register(array $row){
        $row = $this->toParams(
            $row,
            [
                'user-role-id' => 'userRoleId',
                'module-id'    => 'moduleId',
                'login'        => null,
                'surname'      => null,
                'name'         => null,
                'middle-name'  => 'middleName',
                'phone'        => null,
                'password'     => null
            ]
        );

        $row['password'] = hash('sha256', $row['password']);

        $sql = <<<SQL
INSERT INTO "Core"."User"
    (
        "IDUser",
        "UserRoleID",
        "ModuleID",
        "Login",
        "Surname",
        "Name",
        "MiddleName",
        "Phone",
        "IsActive",
        "Password",
        "IsPasswordExpired"
    )
VALUES
    (
        DEFAULT,
        :userRoleId,
        :moduleId,
        :login,
        :surname,
        :name,
        :middleName,
        :phone,
        TRUE,
        DECODE(:password, 'hex'),
        FALSE
    )
RETURNING
    "IDUser";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function modify($id, array $row){
        $row = $this->toParams(
            $row,
            [
                'user-role-id' => 'userRoleId',
                'module-id'    => 'moduleId',
                'login'        => null,
                'surname'      => null,
                'name'         => null,
                'middle-name'  => 'middleName',
                'phone'        => null
            ]
        );

        $row['id'] = $id;

        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "UserRoleID" = :userRoleId,
    "ModuleID"   = :moduleId,
    "Login"      = :login,
    "Surname"    = :surname,
    "Name"       = :name,
    "MiddleName" = :middleName,
    "Phone"      = :phone
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute($row);
    }

    public function setActivity($id, $is){
        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "IsActive" = :is
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'is' => $is
        ]);
    }

    public function setPasswordExpired($id, $is){
        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "IsPasswordExpired" = :is
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'is' => $is
        ]);
    }

    public function changePassword($id, $password){
        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "Password"          = DECODE(:password, 'hex'),
    "IsPasswordExpired" = FALSE
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id'       => $id,
            'password' => hash('sha256', $password)
        ]);
    }

    public function changeRole($id, $userRoleId){
        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "UserRoleID" = :userRoleId
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id'         => $id,
            'userRoleId' => $userRoleId
        ]);
    }

    public function changeModule($id, $moduleId){
        $sql = <<<SQL
UPDATE
    "Core"."User"
SET
    "ModuleID" = :moduleId
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id'       => $id,
            'moduleId' => $moduleId
        ]);
    }

    public function remove($id){
        $sql = <<<SQL
DELETE FROM
    "Core"."User"
WHERE
    ("IDUser" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }
}
?>
