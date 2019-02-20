<?php
namespace Environment\DataLayers\OnlineStatements\Statements;

class Statements extends \Unikum\Core\DataLayer {
    const
        DEFAULT_CONNECTION = 'OnlineStatements';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getById($id){
        $sql = <<<SQL
SELECT
    "s-stmt"."IDStatement" as "id",
    "s-stmt"."INN" as "inn",
    "s-stmt"."JSON" as "data",
    "s-stmt"."Password" as "password",
    TO_CHAR("s-stmt"."CreatingDateTime", 'DD.MM.YYYY HH24:MI:SS') as "stamp",
    TO_CHAR(NOW() - "s-stmt"."CreatingDateTime", 'DD д. HH24 ч. MI мин.') as "age",
    "s-stmt-st"."IDStatus" as "status-id",
    "s-stmt-st"."Name" as "status-name"
FROM
    "Statements"."Statement" as "s-stmt"
        INNER JOIN
            (
                SELECT
                    "s-stmt-sub"."IDStatement" as "statement-id",
                    MAX(COALESCE("s-stmt-st-sub"."StatusID", :defaultStatus)) as "status-id"
                FROM
                    "Statements"."Statement" as "s-stmt-sub"
                        LEFT JOIN "Statements"."StatementStatus" as "s-stmt-st-sub"
                            ON "s-stmt-sub"."IDStatement" = "s-stmt-st-sub"."StatementID"
                GROUP BY
                    1
            ) as "statement-current-status"
                ON ("s-stmt"."IDStatement" = "statement-current-status"."statement-id")
        LEFT JOIN "Statements"."Status" as "s-stmt-st"
            ON "statement-current-status"."status-id" = "s-stmt-st"."IDStatus"
WHERE
    ("s-stmt"."IDStatement" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'defaultStatus' => Statuses::REVISION,
            'id'            => $id
        ]);

        return $stmt->fetch();
    }

    public function getBy(array $filters, $limit = null, $offset = null){
        $params = [];
        $values = [ Statuses::REVISION ];
        $limits = [];

        if(array_key_exists('inn', $filters)){
            $params[] = '("s-stmt"."INN" = ?)';

            $values[] = $filters['inn'];
        }

        if(array_key_exists('status-id', $filters)){
            if(is_array($filters['status-id'])){
                $count = count($filters['status-id']);

                if($count > 0){
                    $places   = implode(',', array_fill(0, $count, '?'));
                    $params[] = '("statement-current-status"."status-id" IN (' . $places . '))';
                    $values   = array_merge($values, $filters['status-id']);
                }
            } else {
                $params[] = '("statement-current-status"."status-id" = ?)';
                $values[] = $filters['status-id'];
            }
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    COUNT(*)
FROM
    "Statements"."Statement" as "s-stmt"
        INNER JOIN
            (
                SELECT
                    "s-stmt-sub"."IDStatement" as "statement-id",
                    MAX(COALESCE("s-stmt-st-sub"."StatusID", ?)) as "status-id"
                FROM
                    "Statements"."Statement" as "s-stmt-sub"
                        LEFT JOIN "Statements"."StatementStatus" as "s-stmt-st-sub"
                            ON "s-stmt-sub"."IDStatement" = "s-stmt-st-sub"."StatementID"
                GROUP BY
                    1
            ) as "statement-current-status"
                ON ("s-stmt"."IDStatement" = "statement-current-status"."statement-id")
{$params}
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        $count = $stmt->fetchColumn();

        if($limit !== null){
            $limits[] = 'LIMIT ?';

            $values[] = $limit;

            if($offset !== null){
                $limits[] = 'OFFSET ?';

                $values[] = $offset;
            }
        }

        $limits = $limits ? implode(PHP_EOL, $limits) : '';

        $sql = <<<SQL
SELECT
    "s-stmt"."IDStatement" as "id",
    "s-stmt"."INN" as "inn",
    "s-stmt"."JSON"->'main'->>'name' as "name",
    TO_CHAR("s-stmt"."CreatingDateTime", 'DD.MM.YYYY HH24:MI:SS') as "stamp",
    TO_CHAR(NOW() - "s-stmt"."CreatingDateTime", 'DD д. HH24 ч. MI мин.') as "age",
    "s-stmt-st"."IDStatus" as "status-id",
    "s-stmt-st"."Name" as "status-name"
FROM
    "Statements"."Statement" as "s-stmt"
        INNER JOIN
            (
                SELECT
                    "s-stmt-sub"."IDStatement" as "statement-id",
                    MAX(COALESCE("s-stmt-st-sub"."StatusID", ?)) as "status-id"
                FROM
                    "Statements"."Statement" as "s-stmt-sub"
                        LEFT JOIN "Statements"."StatementStatus" as "s-stmt-st-sub"
                            ON "s-stmt-sub"."IDStatement" = "s-stmt-st-sub"."StatementID"
                GROUP BY
                    1
            ) as "statement-current-status"
                ON ("s-stmt"."IDStatement" = "statement-current-status"."statement-id")
        LEFT JOIN "Statements"."Status" as "s-stmt-st"
            ON "statement-current-status"."status-id" = "s-stmt-st"."IDStatus"
{$params}
ORDER BY
    "s-stmt"."CreatingDateTime"
{$limits};
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        $rows = $stmt->fetchAll();

        return [ $count, $rows ];
    }

    public function delete($id){
        $sql = <<<SQL
DELETE FROM
    "Statements"."Statement"
WHERE
    ("IDStatement" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }
}
?>