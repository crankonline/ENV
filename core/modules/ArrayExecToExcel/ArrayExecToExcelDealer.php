<?php


namespace Environment\modules\ArrayExecToExcel;


use Unikum\Core\Dbms\ConnectionManager as Connections;

abstract class ArrayExecToExcelDealer
{

    protected $params;
    protected $values;

    abstract public function setParams(array $values);
    public function getDateMax($date)
    {
        return date("Y-m-d", strtotime("+1 days", strtotime($date)));
    }

    public function geRes()
    {

        $param = $this->params;
        $value = $this->values;

        $sql = <<<SQL
SELECT "p"."Account", "p"."Sum", "p"."DateTime", "p"."TXNID", "ps"."Name", "inv". "inn" FROM "Dealer_payments"."PayLog" as "p" 

INNER JOIN "Dealer_payments"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"  

INNER JOIN "Dealer_data"."invoice" AS "inv" ON "p"."Account" = "inv"."invoice_serial_number" 
WHERE  
    {$param}
ORDER BY 
    "p"."IDPayLog" DESC, 
    "p"."DateTime"

SQL;

        $stmt = Connections::getConnection('Dealer')->prepare($sql);

        $stmt->execute($value);

        return $stmt->fetchAll();

    }
}