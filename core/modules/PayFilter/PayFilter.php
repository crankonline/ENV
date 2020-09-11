<?php


namespace Environment\Modules\PayFilter;


use Unikum\Core\Dbms\ConnectionManager as Connections;

abstract  class  PayFilter
{

        protected $params;
        protected $values;

    abstract public function setParams($values);
    public function getDateMax($date)
    {
        return date("Y-m-d", strtotime("+1 days", strtotime($date)));
    }

    public function geRes()
    {


        $param = $this->params;
        $value = $this->values;

        $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Log" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$param}
ORDER BY 
    "p"."IDLog" DESC,
    "p"."DateTime"

SQL;


        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute($value);

        return $stmt->fetchAll();

    }
}
