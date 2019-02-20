<?php
namespace Environment\DataLayers\Environment\Core;

class ModulePermissions extends \Environment\Core\DataLayer {
    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('user-role-id', $filters)){
            $params[] = <<<SQL
EXISTS(
    SELECT
        TRUE
    FROM
        "Core"."UserRoleModulePermission" as "c-urmp"
    WHERE
        ("c-urmp"."ModulePermissionID" = "c-mp"."IDModulePermission")
        AND
        ("c-urmp"."UserRoleID" = :userRoleId)
)
SQL;

            $values['userRoleId'] = $filters['user-role-id'];
        }

        if(array_key_exists('module-id', $filters)){
            $params[] = '("c-m"."IDModule" = :moduleId)';

            $values['moduleId'] = $filters['module-id'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "c-mp"."IDModulePermission" as "id",
    "c-mg"."IDModuleGroup" as "module-group-id",
    "c-mg"."Name" as "module-group-name",
    "c-m"."IDModule" as "module-id",
    "c-m"."AccessKey" as "module-access-key",
    "c-m"."IsEntryPoint" as "module-is-entry-point",
    "c-m"."Name" as "module-name",
    "c-mp"."Mark" as "mark",
    "c-mp"."Name" as "name"
FROM
    "Core"."ModulePermission" as "c-mp"
        INNER JOIN "Core"."Module" as "c-m"
            ON "c-mp"."ModuleID" = "c-m"."IDModule"
        LEFT JOIN "Core"."ModuleGroup" as "c-mg"
            ON "c-m"."ModuleGroupID" = "c-mg"."IDModuleGroup"
{$params}
ORDER BY
    "c-m"."IDModule",
    "c-mp"."IDModulePermission";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    public function allowToRole(array $row){
        $row = $this->toParams(
            $row,
            [
                'user-role-id'  => 'userRoleId',
                'permission-id' => 'permissionId'
            ]
        );

        $sql = <<<SQL
INSERT INTO "Core"."UserRoleModulePermission"
    ("UserRoleID", "ModulePermissionID")
VALUES
    (:userRoleId, :permissionId);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute($row);
    }

    public function forbidAllToRole($userRoleId){
        $sql = <<<SQL
DELETE FROM
    "Core"."UserRoleModulePermission"
WHERE
    ("UserRoleID" = :userRoleId);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'userRoleId' => $userRoleId
        ]);
    }
}
?>