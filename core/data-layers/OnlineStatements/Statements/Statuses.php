<?php
namespace Environment\DataLayers\OnlineStatements\Statements;

class Statuses extends \Unikum\Core\DataLayer {
    const
        DEFAULT_CONNECTION = 'OnlineStatements';

    const
        REVISION       = 1,
        REVISED        = 2,
        IDENTIFICATION = 3,
        IDENTIFIED     = 4,
        PAYABLE        = 5,
        PAID           = 6,
        COMPLETE       = 7,
        REJECTED       = 8;

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('status-id', $filters)){
            if(is_array($filters['status-id'])){
                $count = count($filters['status-id']);

                if($count > 0){
                    $places   = implode(',', array_fill(0, $count, '?'));
                    $params[] = '("s-s"."IDStatus" IN (' . $places . '))';
                    $values   = array_merge($values, $filters['status-id']);
                }
            } else {
                $params[] = '("s-s"."IDStatus" = ?)';
                $values[] = $filters['status-id'];
            }
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "IDStatus" as "id",
    "Name" as "name"
FROM
    "Statements"."Status" as "s-s"
{$params}
ORDER BY
    "Order";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }
}
?>