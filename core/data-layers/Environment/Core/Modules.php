<?php
namespace Environment\DataLayers\Environment\Core;

class Modules extends \Environment\Core\DataLayer {
    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('user-role-id', $filters)){
            $params[] = <<<SQL
EXISTS(
    SELECT
        TRUE
    FROM
        "Core"."ModulePermission" as "c-mp"
            INNER JOIN "Core"."UserRoleModulePermission" as "c-urmp"
                ON "c-mp"."IDModulePermission" = "c-urmp"."ModulePermissionID"
    WHERE
        ("c-urmp"."UserRoleID" = :userRoleId)
        AND
        ("c-mp"."ModuleID" = "c-m"."IDModule")
)
SQL;

            $values['userRoleId'] = $filters['user-role-id'];
        }

        if(array_key_exists('is-entry-point', $filters)){
            $params[] = $filters['is-entry-point']
                ? '"c-m"."IsEntryPoint"'
                : '(NOT "c-m"."IsEntryPoint")';
        }

        if(array_key_exists('access-key', $filters)){
            $params[] = '("c-m"."AccessKey" = :accessKey)';

            $values['accessKey'] = $filters['access-key'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "c-m"."IDModule" as "id",
    "c-mg"."IDModuleGroup" as "module-group-id",
    "c-mg"."Name" as "module-group-name",
    "c-m"."AccessKey" as "access-key",
    "c-m"."HandlerClass" as "handler-class",
    "c-m"."Name" as "name"
FROM
    "Core"."Module" as "c-m"
        LEFT JOIN "Core"."ModuleGroup" as "c-mg"
            ON "c-m"."ModuleGroupID" = "c-mg"."IDModuleGroup"
{$params}
ORDER BY
    "c-mg"."IDModuleGroup",
    "c-m"."IDModule";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }
}
?>