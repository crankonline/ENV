<?php
namespace Environment\DataLayers\OnlineStatements\Statements;

class Files extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'OnlineStatements';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getByStatement($statementId){
        $sql = <<<SQL
SELECT
    "s-f"."IDFile" as "id",
    "s-ft"."IDFileType" as "file-type-id",
    "s-ft"."Name" as "file-type-name",
    "s-f"."StoreFileID" as "store-file-id",
    "s-f"."ContentType" as "content-type",
    "s-f"."FileName" as "filename",
    TO_CHAR("s-f"."AddDateTime", 'DD.MM.YYYY HH24:MI:SS') as "date-time"
FROM
    "Statements"."File" as "s-f"
        INNER JOIN "Statements"."FileType" as "s-ft"
            ON "s-f"."FileTypeID" = "s-ft"."IDFileType"
WHERE
    ("s-f"."StatementID" = :statementId)
ORDER BY
    "s-ft"."IDFileType",
    "s-f"."IDFile";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'statementId' => $statementId
        ]);

        return $stmt->fetchAll();
    }

    /** TODO deprected     */
    public function deleteByStatement($statementId){
        $sql = <<<SQL
DELETE FROM
    "Statements"."File"
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