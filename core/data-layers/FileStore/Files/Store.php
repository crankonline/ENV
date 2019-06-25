<?php
/**
 * Reregister
 */
namespace Environment\DataLayers\FileStore\Files;

class Store extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'FileStore';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getById($id){
        $sql = <<<SQL
SELECT
    "f-s"."IDFile" as "id",
    "f-s"."Name" as "name",
    "f-s"."Size" as "size",
    "f-s"."Content" as "content",
    "f-s"."DateTime" as "date-time"
FROM
    "Files"."Store" as "f-s"
WHERE
    ("IDFile" = :id);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }
}
?>