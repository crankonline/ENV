<?php


namespace Environment\Modules\PayFilter;


use Unikum\Core\Dbms\ConnectionManager as Connections;

abstract  class  PayFilterSochi
{

        protected $params;
        protected $values;
        protected $limits;

    abstract public function setParams($values, $limits, $offset);

    public function getDateMax($date)
    {
        return date("Y-m-d", strtotime("+1 days", strtotime($date)));
    }

    public function geRes()
    {

        $param = $this->params;
        $value = $this->values;
        $lim   = $this->limits;

        $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$param}
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
{$lim};
SQL;
        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute($value);

        return $stmt->fetchAll();

    }
}
