<?php

namespace Environment\Modules;


use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Modules\PayFilter as PayFilter;


class LogTerminal extends \Environment\Core\Module
{
    const ROWS_PER_PAGE = 50;

    protected $config = [
        'template' => 'layouts/LogTerminal/Default.html',
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

    private function getType() {
        $type = array("check", "pay");
        return $type;
    }

    private function getTypeSel($value) {

        $values['f_type'] = $value['type'];

        $params[] = 'WHERE "p"."Type" = :f_type';

        $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Log" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$params[0]}
ORDER BY 
    "p"."IDLog" DESC,
    "p"."DateTime"

SQL;
        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    private function getPaySys($value) {

        $values['f_paymentSystem'] = $value;

        $params[] = 'WHERE "p"."PaymentSystemID" = :f_paymentSystem';

        $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Log" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  
    {$params[0]}
ORDER BY 
    "p"."IDLog" DESC,
    "p"."DateTime"

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


                if ($new_array['account'] && !$new_array['type'] && !$new_array['paymentSystem']) {

                    $account = new  PayFilter\Account();
                    $account->setParams($new_array);

                }

                if ($new_array['account'] && $new_array['type'] && !$new_array['paymentSystem']) {

                    $account = new  PayFilter\AccountAndDateAndType();
                    $account->setParams($new_array);

                }

                if ($new_array['account'] && !$new_array['type'] && $new_array['paymentSystem']) {

                    $account = new  PayFilter\AccountAndDateAndPaySys();
                    $account->setParams($new_array);

                }

                if ($new_array['account'] && $new_array['type'] && $new_array['paymentSystem']) {

                    $account = new  PayFilter\AccountAndDateAndPaySysAndType();
                    $account->setParams($new_array);

                }

                if (!$new_array['account'] && $new_array['type'] && $new_array['paymentSystem']) {

                    $account = new  PayFilter\PaySysAndDateAndType();
                    $account->setParams($new_array);

                }

                if (!$new_array['account'] && !$new_array['type'] && $new_array['paymentSystem']) {

                    $account = new  PayFilter\PaySysAndDate();
                    $account->setParams($new_array);

                }

                if (!$new_array['account'] && $new_array['type'] && !$new_array['paymentSystem']) {

                    $account = new  PayFilter\DateAndType();
                    $account->setParams($new_array);

                }

                if (!$new_array['account'] && !$new_array['type'] && !$new_array['paymentSystem']) {

                    $account = new  PayFilter\Date();
                    $account->setParams($new_array);

                }
                $rows = $account->geRes();

            } else {
                $sql  = <<<SQL
SELECT
    COUNT("p"."IDLog")
FROM
     "Payment"."Log" AS "p"
SQL;
                $stmt = Connections::getConnection( 'Pay' )->prepare( $sql );

                $stmt->execute();

                $count = $stmt->fetchColumn();


                $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Log" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"    
ORDER BY 
    "p"."IDLog" DESC,
    "p"."DateTime"
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

            $this->variables->account          = $_GET['account'] ?? null;
            $this->variables->dateMin          = $_GET['dateMin'] ?? null;
            $this->variables->dateMax          = $_GET['dateMax'] ?? null;
            $this->variables->paySys    = $_GET['paySys'] ?? null;
            $this->variables->type             = $_GET['type'] ?? null;
            $this->variables->page             = $_GET['page'] ?? null;
            $this->variables->PaySys           = $this->getPaymentSys();
            $this->variables->gtType           = $this->getType();

            $dateMin = $_GET['dateMin'] ? $_GET['dateMin'] : date('Y-m-01 00:00:00');
            $dateMax = $_GET['dateMax'] ? $_GET['dateMax'] : date('Y-m-d  23:59:59');

            $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
            $limit  = self::ROWS_PER_PAGE;
            $offset = ( $page - 1 ) * $limit;

                   if ($chkFilter)
                    {

                        list($count, $logs) = $this->getLog(
                            [
                            'account' => $_GET['account'],
                            'type' => $_GET['type'],
                            'paymentSystem' => $_GET['paySys'],
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