<?php

namespace Environment\Modules;


use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Modules\PayFilter as PayFilter;


class PaymentSochi extends \Environment\Core\Module
{
    const ROWS_PER_PAGE = 50;

    protected $config = [
        'template' => 'layouts/PaymentSochi/Default.html',
        'listen' => 'action',
        'plugins'  => [
            'paginator' => Plugins\Paginator::class
        ]
    ];

    private function getPaymentSys() {
        $sql = <<<SQL
SELECT * FROM "Payment"."PaymentSystem"

SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    private function getPaySys($value) {

        $values['f_paymentSystem'] = $value;

        $params[] = 'WHERE "p"."PaymentSystemID" = :f_paymentSystem';

        $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$params[0]}
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"

SQL;
        $stmt = Connections::getConnection('Pay')->prepare($sql);

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

                $new_array = array_filter($filters, function($element) {
                    return !empty($element);
                });

                if ($filters['account'] && !$filters['paymentSystem']) {
                    $account = new  PayFilter\AccountSochi();
                    $coun    = new  PayFilter\CountAccountSochi();

                    $account->setParams($new_array, $limits, $offset);
                    $coun->setParams($new_array);

                }


                if ($filters['account'] && $filters['paymentSystem']) {

                    $account = new  PayFilter\AccountAndDateAndPaySysSochi();
                    $coun    = new  PayFilter\CountAccountAndDateAndPaySysSochi();

                    $account->setParams($new_array, $limits, $offset);
                    $coun->setParams($new_array);

                }

                if (!$filters['account'] && $filters['paymentSystem']) {

                    $account = new  PayFilter\PaySysAndDateSochi();
                    $coun    = new  PayFilter\CountPaySysDateSochi();

                    $account->setParams($new_array, $limits, $offset);
                    $coun->setParams($new_array);

                }

                if (!$filters['account'] && !$filters['paymentSystem']) {

                    $account = new  PayFilter\DateSochi();
                    $coun    = new  PayFilter\CountDateSochi();

                    $account->setParams($new_array, $limits, $offset);
                    $coun->setParams($new_array);


                }

                $rows = $account->geRes();
                $count = $coun->geRes();

            } else {
                $sql  = <<<SQL
SELECT
    COUNT("p"."IDPayment")
FROM
     "Payment"."Payment" AS "p"
SQL;
                $stmt = Connections::getConnection( 'Pay' )->prepare( $sql );

                $stmt->execute();

                $count = $stmt->fetchColumn();


                $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"    
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
{$limits};
SQL;



                $stmt = Connections::getConnection('Pay')->prepare($sql);

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

    protected function main() {
        $this->variables->mes = [];
        $this->variables->errors = [];
        $chkFilter =  $_GET['btn_submit'] ?? null;

        try {

            $account =  $_GET['account'] ?? null;
            $paySys =  $_GET['paySys'] ?? null;
            $type =  $_GET['type'] ?? null;
            $dateMin = !empty($_GET['dateMin']) ? $_GET['dateMin'].' 00:00:00' : date('Y-m-01 00:00:00');
            $dateMax = !empty($_GET['dateMax']) ? $_GET['dateMax'].' 23:59:59' : date('Y-m-d  23:59:59');


            $this->variables->account          = $account;
            $this->variables->dateMin          =  date( 'Y-m-d', strtotime($dateMin));
            $this->variables->dateMax          = date('Y-m-d',  strtotime($dateMax));
            $this->variables->paySysD           = $paySys;
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
                            'type' => $type,
                            'paymentSystem' => $paySys,
                            'dateMin' => $dateMin,
                            'dateMax' => $dateMax,
                            ],
                            $limit, $offset
                        );

                        if ($logs) {
                            $this->variables->logs = &$logs;
                            $this->context->paginator['count'] = (int)ceil($count / $limit);

                            $this->variables->count = $count;

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