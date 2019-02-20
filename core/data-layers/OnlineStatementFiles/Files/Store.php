<?php
namespace Environment\DataLayers\OnlineStatementFiles\Files;

class Store extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'OnlineStatementFiles';

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

    public function delete($id){
        $params = [];
        $values = [];

        if(is_array($id)){
            $count = count($id);

            if($count > 0){
                $places   = implode(',', array_fill(0, $count, '?'));
                $params[] = '("IDFile" IN (' . $places . '))';
                $values   = array_merge($values, $id);
            }
        } else {
            $params[] = '("IDFile" = ?)';
            $values[] = $id;
        }

        if(!$params){
            return false;
        }

        $params = 'WHERE ' . implode(' AND ', $params);

        $sql = <<<SQL
DELETE FROM
    "Files"."Store"
{$params};
SQL;

        $stmt = $this->dbms->prepare($sql);

        return $stmt->execute($values);
    }
}
?>