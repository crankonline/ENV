<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Modules\PayFilter as PayFilter;
use Environment\Modules\ExportToExcel as Excel;
use Environment\Modules\ArrayExecToExcel as ExecExcel;



class PaymentDealer extends \Environment\Core\Module
{
    const ROWS_PER_PAGE = 50;


    protected $config = [
        'template' => 'layouts/PaymentDealer/Default.html',
        'listen' => 'action',
        'plugins'  => [
            'paginator' => Plugins\Paginator::class
        ]
    ];

    private function getPaymentSys() {
        $sql = <<<SQL
SELECT * FROM "Dealer_payments"."PaymentSystem"

SQL;

        $stmt = Connections::getConnection('Dealer')->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    private function getPaySys($value) {

        $values['f_paymentSystem'] = $value;

        $params[] = 'WHERE "p"."PaymentSystemID" = :f_paymentSystem';

        $sql = <<<SQL
SELECT "p".*, "ps"."Name", "inv". "inn" FROM "Dealer_payments"."PayLog" as "p" 

INNER JOIN "Dealer_payments"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$params[0]}
ORDER BY 
    "p"."IDPayLog" DESC,
    "p"."DateTime"

SQL;
        $stmt = Connections::getConnection('Dealer')->prepare($sql);

        $stmt->execute($values);

        return  $stmt->fetchAll();

    }


    private function getLog( array $filters, $limit = null, $offset = null  )
    {
        try{

            $limits = null;
            if ( $limit !== null ) {
                $limits[] = 'LIMIT :limit';

                $values['limit'] = $limit;

                if ( $offset !== null ) {
                    $limits[] = 'OFFSET :offset';

                    $values['offset'] = $offset;
                }
            }

            $limits = ! empty( $limits ) ? implode( PHP_EOL, $limits ) : '';


            if ($filters){



                $count = '';

                $new_array = array_filter($filters, function($element) {
                    return !empty($element);
                });


                if ($filters['account'] && !$filters['paymentSystem'] && !$filters['inn']) {

                    $account = new  PayFilter\AccountDealer();
                    $account->setParams($new_array);

                }


                if ($filters['account'] && $filters['paymentSystem'] && !$filters['inn']) {

                    $account = new  PayFilter\AccountAndDateAndPaySysDealer();
                    $account->setParams($new_array);

                }

                if (!$filters['account'] && $filters['paymentSystem'] && !$filters['inn']) {

                    $account = new  PayFilter\PaySysAndDateDealer();
                    $account->setParams($new_array);

                }

                if (!$filters['account'] && !$filters['paymentSystem'] && !$filters['inn']) {

                    $account = new  PayFilter\DateDealer();
                    $account->setParams($new_array);

                }

                if (!$filters['account'] && !$filters['paymentSystem'] && $filters['inn']) {

                    $account = new  PayFilter\InnDealer();
                    $account->setParams($new_array);

                }

                if ($filters['account'] && !$filters['paymentSystem'] && $filters['inn']) {

                    $account = new  PayFilter\AccountAndInnDealer();
                    $account->setParams($new_array);

                }

                if (!$filters['account'] && $filters['paymentSystem'] && $filters['inn']) {

                    $account = new  PayFilter\PaySysAndDateAndInnDealer();
                    $account->setParams($new_array);

                }

                if ($filters['account'] && $filters['paymentSystem'] && $filters['inn']) {

                    $account = new  PayFilter\AccountAndDateAndPaySysAndInnDealer();
                    $account->setParams($new_array);

                }

                $rows = $account->geRes();

            } else {
                $sql  = <<<SQL
SELECT
    COUNT("p"."IDPayLog")
FROM
     "Dealer_payments"."PayLog" AS "p"
SQL;
                $stmt = Connections::getConnection( 'Dealer' )->prepare( $sql );

                $stmt->execute();

                $count = $stmt->fetchColumn();


                $sql = <<<SQL
SELECT "p".*, "ps"."Name", "inv". "inn" FROM "Dealer_payments"."PayLog" as "p" 

INNER JOIN "Dealer_payments"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"  

INNER JOIN "Dealer_data"."invoice" AS "inv" ON "p"."Account" = "inv"."invoice_serial_number"   
ORDER BY 
    "p"."IDPayLog" DESC, 
    "p"."DateTime"
{$limits};
SQL;



                $stmt = Connections::getConnection('Dealer')->prepare($sql);

                $stmt->execute([
                    'limit'            => 50,
                    'offset'           => $offset
                ] );

                $rows =  $stmt->fetchAll();

            }

        } catch (\SoapFault $e) {
            \Sentry\captureException( $e );
            exit;
        }
        return [&$count, &$rows];
    }

    function conExcel ()
    {
        $result = file_get_contents('php://input');
        $result = json_decode($result, true);
        $account = $result['account'] ?? null;
        $inn = $result['inn'] ?? null;
        $system = $result['system'] ?? null;
        $dateMin = !empty($result['dateMin'] ) ? $result['dateMin'] .' 00:00:00' : date('Y-m-01 00:00:00');
        $dateMax = !empty($result['dateMax']) ? $result['dateMax'].' 23:59:59' : date('Y-m-d  23:59:59');
        $nw_array = array_filter($result, function($element) {
            return !empty($element);
        });

        if ($account && !$inn && !$system && $dateMin && $dateMax) {

            $account = new  ExecExcel\AccountDealer();
            $account->setParams($nw_array);
        }

        if ($account && $inn && !$system && $dateMin && $dateMax) {

            $account = new  ExecExcel\AccountInnDealer();
            $account->setParams($nw_array);
        }

        if (!$account && $inn && !$system && $dateMin && $dateMax) {

            $account = new  ExecExcel\InnDealer();
            $account->setParams($nw_array);
        }

        if (!$account && $inn && $system && $dateMin && $dateMax) {

            $account = new  ExecExcel\InnSystemDealer();
            $account->setParams($nw_array);
        }

        if (!$account && !$inn && $system && $dateMin && $dateMax) {

            $account = new  ExecExcel\SystemDealer();
            $account->setParams($nw_array);
        }

        if ($account && !$inn && $system && $dateMin && $dateMax) {

            $account = new  ExecExcel\AccountSystemDealer();
            $account->setParams($nw_array);
        }

        if (!$account && !$inn && !$system && $dateMin && $dateMax) {
            $account = new  ExecExcel\DateDealer();
            $account->setParams($nw_array);
        }

        if ($account && $inn && $system && $dateMin && $dateMax) {
            $account = new  ExecExcel\AccountInnSystemDealer();
            $account->setParams($nw_array);
        }

        if (!$account && !$inn && !$system && !$dateMin && !$dateMax) {
            $sql = <<<SQL
SELECT "p".*, "ps"."Name", "inv". "inn" FROM "Dealer_payments"."PayLog" as "p" 

INNER JOIN "Dealer_payments"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"  

INNER JOIN "Dealer_data"."invoice" AS "inv" ON "p"."Account" = "inv"."invoice_serial_number"   
ORDER BY 
    "p"."IDPayLog" DESC, 
    "p"."DateTime"
SQL;

        $stmt = Connections::getConnection('Dealer')->prepare($sql);

        $stmt->execute();

        $res = $stmt->fetchAll();
    }
        $res = $account->geRes();
        $exc = new  Excel\ExportToExcel();
        $exc->getExcel($res, 'dealer');


    }


    protected function main() {
        $this->variables->mes = [];
        $this->variables->errors = [];
        $chkFilter = $_GET['btn_submit'] ?? null;

        try {

            $account =  $_GET['account'] ?? null;
            $inn     =  $_GET['inn'] ?? null;
            $paySys  =  $_GET['paySys'] ?? null;
            $type    =  $_GET['type'] ?? null;
            $dateMin = !empty($_GET['dateMin']) ? $_GET['dateMin'].' 00:00:00' : date('Y-m-01 00:00:00');
            $dateMax = !empty($_GET['dateMax']) ? $_GET['dateMax'].' 23:59:59' : date('Y-m-d  23:59:59');


            $this->variables->inn              = $inn;
            $this->variables->account          = $account;
            $this->variables->dateMin          =  date( 'Y-m-d', strtotime($dateMin));
            $this->variables->dateMax          = date('Y-m-d',  strtotime($dateMax));
            $this->variables->paySysD          = $paySys;
            $this->variables->type             = $type;
            $this->variables->page             = $_GET['page'] ?? null;
            $this->variables->PaySys           = $this->getPaymentSys();

            $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
            $limit  = self::ROWS_PER_PAGE;
            $offset = ( $page - 1 ) * $limit;

                   if ($chkFilter)
                    {

                        list($count, $logs) = $this->getLog(
                            [
                            'account' => $account,
                            'inn' => $inn,
                            'type' => $type,
                            'paymentSystem' => $paySys,
                            'dateMin' => $dateMin,
                            'dateMax' => $dateMax,
                            ],
                            $limit, $offset
                        );

                        if ($logs) {
                            $this->variables->logs = &$logs;
                        } else {
                            $this->variables->mes[] = 'Не найдено записей';
                        }
                    } else {

                        list($count, $logs) = $this->getLog([], $limit, $offset);

                        $this->context->paginator['count'] = (int)ceil($count / $limit);

                        $this->variables->count = $count;
                        $this->variables->logs = &$logs;
                    }


        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }
}