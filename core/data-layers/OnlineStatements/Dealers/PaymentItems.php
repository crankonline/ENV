<?php
namespace Environment\DataLayers\OnlineStatements\Dealers;

class PaymentItems extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'OnlineStatements';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getBy(array $filters){
        $params = [];
        $values = [];

        if(array_key_exists('payment-id', $filters)){
            $params[] = '("d-pmtitm"."PaymentID" = :paymentId)';

            $values['paymentId'] = $filters['payment-id'];
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "d-pmtitm"."IDPaymentItem" as "id",
    "d-pmtctg"."IDPaymentCategory" as "payment-category-id",
    "d-pmtctg"."Name" as "payment-category-name",
    "d-pmtitm"."Sum" as "sum"
FROM
    "Dealers"."PaymentCategory" as "d-pmtctg"
        INNER JOIN "Dealers"."PaymentItem" as "d-pmtitm"
            ON "d-pmtctg"."IDPaymentCategory" = "d-pmtitm"."PaymentCategoryID"
{$params}
ORDER BY
    "d-pmtctg"."IDPaymentCategory";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }
}
?>