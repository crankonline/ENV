<?php
namespace Environment\DataLayers\OnlineStatements\Dealers;

class Payments extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'OnlineStatements';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('inn', $filters)){
            $params[] = '("d-pmt"."INN" = :inn)';

            $values['inn'] = $filters['inn'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "d-pmt"."IDPayment" as "id",
    "d-pmt"."INN" as "inn",
    TO_CHAR("d-pmt"."DateTime", 'DD.MM.YYYY HH24:MI:SS') as "date-time",
    TRIM(CONCAT_WS(' ', "d-d"."Surname", "d-d"."Name")) as "dealer-name",
    "d-d"."Login" as "dealer-login"
FROM
    "Dealers"."Payment" as "d-pmt"
        INNER JOIN "Dealers"."Dealer" as "d-d"
            ON "d-pmt"."DealerID" = "d-d"."IDDealer"
{$params}
ORDER BY
    "d-pmt"."IDPayment";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }
}
?>