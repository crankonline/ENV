<?php
namespace Environment\DataLayers\OnlineStatements\Statements;

class StatementStatuses extends \Unikum\Core\DataLayer {
    const
        DEFAULT_CONNECTION = 'OnlineStatements';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('statement-id', $filters)){
            $params[] = '("s-stmt-st"."StatementID" = :statementId)';

            $values['statementId'] = $filters['statement-id'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "s-st"."IDStatus" as "id",
    "s-st"."Name" as "name",
    TO_CHAR("s-stmt-st"."DateTime", 'DD.MM.YYYY HH24:MI:SS') as "stamp",
    TO_CHAR(NOW() - "s-stmt-st"."DateTime", 'DD д. HH24 ч. MI мин.') as "age",
    "s-stmt-st"."Description" as "description",
    "s-stmt-st"."Operator" as "operator"
FROM
    "Statements"."Status" as "s-st"
        INNER JOIN "Statements"."StatementStatus" as "s-stmt-st"
            ON "s-st"."IDStatus" = "s-stmt-st"."StatusID"
{$params}
ORDER BY
    "s-stmt-st"."DateTime", "s-st"."IDStatus";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    public function insert(array $row){
        $row = $this->toParams(
            $row,
            [
                'statement-id' => 'statementId',
                'status-id'    => 'statusId',
                'description'  => null,
                'operator'     => null
            ]
        );

        $sql = <<<SQL
INSERT INTO "Statements"."StatementStatus"
    ("IDStatementStatus", "StatementID", "StatusID", "DateTime", "Description", "Operator")
VALUES
    (DEFAULT, :statementId, :statusId, DEFAULT, :description, :operator)
RETURNING
    "IDStatementStatus";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function deleteByStatement($statementId){
        $sql = <<<SQL
DELETE FROM
    "Statements"."StatementStatus"
WHERE
    ("StatementID" = :statementId);
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute([
            'statementId' => $statementId
        ]);
    }
}
?>