<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

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

                $params = [];

                $params[] = ($filters['type'] && !$filters['paymentSystem'] && !$filters['dateMin']) ? 'AND ("p"."Type" = :f_type)': null;

                $params[] = ($filters['paymentSystem']  && !$filters['type'] && !$filters['dateMin']) ? 'AND ("p"."PaymentSystemID" = :f_paymentSystem)': null;

                $params[] = ($filters['dateMin'] && !$filters['type'] && !$filters['paymentSystem']) ? 'AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)': null;

                $params[] = ($filters['type'] && $filters['paymentSystem'] && !$filters['dateMin']) ? 'AND ("p"."Type" = :f_type) AND ("p"."PaymentSystemID" = :f_paymentSystem)': null;

                $params[] = (!$filters['type'] && $filters['paymentSystem'] && $filters['dateMin']) ? 'AND ("p"."PaymentSystemID" = :f_paymentSystem) AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)': null;

                $params[] =  ($filters['type'] && !$filters['paymentSystem'] && $filters['dateMin']) ? 'AND ("p"."Type" = :f_type) AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)': null;

                $params[] = ($filters['type'] && $filters['paymentSystem'] && $filters['dateMin']) ? 'AND ("p"."Type" = :f_type) AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max) AND ("p"."PaymentSystemID" = :f_paymentSystem)': null;

                $params[] = (!$filters['type'] && !$filters['paymentSystem'] && !$filters['dateMin']) ? null : null;

                $new_array = array_filter($params, function($element) {
                    return !empty($element);
                });

                $new = [];

                foreach($new_array as $key => $value){
                    $new[0] = $value;
                }

                $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Log" AS "p"
     INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem" 
WHERE  "p"."Account" like '%{$filters['account']}%'
    {$new[0]}
ORDER BY 
    "p"."IDLog" DESC,
    "p"."DateTime"

SQL;
                $stmt = Connections::getConnection('Pay')->prepare($sql);

                if ($filters['dateMin'] && $filters['type'] && $filters['paymentSystem']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));
                    $stmt->execute([

                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date,
                        'f_type'   => $filters['type'],
                        'f_paymentSystem'   => $filters['paymentSystem']

                    ]);


                }

                if ($filters['paymentSystem'] && !$filters['type'] && !$filters['dateMin']) {
                    $stmt->execute([
                        'f_paymentSystem'   => $filters['paymentSystem']
                    ]);


                }

                if ($filters['type'] && !$filters['paymentSystem'] && !$filters['dateMin']) {
                    $stmt->execute([
                        'f_type'   => $filters['type']
                    ]);


                }
                if ($filters['type'] && $filters['paymentSystem'] && !$filters['dateMin']) {
                     $stmt->execute([
                        'f_type'   => $filters['type'],
                        'f_paymentSystem'   => $filters['paymentSystem']
                    ]);


                }

                if ($filters['type'] && $filters['dateMin'] && !$filters['paymentSystem']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));

                    $stmt->execute([
                        'f_type'   => $filters['type'],
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);


                }

                if ($filters['paymentSystem'] && $filters['dateMin'] && !$filters['type']) {

                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));

                    $stmt->execute([
                        'f_paymentSystem'   => $filters['paymentSystem'],
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);

                }

                if ($filters['dateMin'] && !$filters['paymentSystem'] && !$filters['type']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));
                    $stmt->execute([
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);

                }

                if (!$filters['dateMin'] && !$filters['paymentSystem'] && !$filters['type']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));
                    $stmt->execute([]);

                }

                $rows =  $stmt->fetchAll();

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
        $chkFilter =  $_GET['chkFilter'] ?? null;

        try {

            $this->variables->account          = $_POST['account'] ?? null;
            $this->variables->dateMin          = $_POST['dateMin'] ?? null;
            $this->variables->dateMax          = $_POST['dateMax'] ?? null;
            $this->variables->paySys    = $_POST['paySys'] ?? null;
            $this->variables->type             = $_POST['type'] ?? null;
            $this->variables->page             = $_GET['page'] ?? null;
         /*   var_dump($this->getPaymentSys());die();*/
            $this->variables->PaySys           = $this->getPaymentSys();



            $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
            $limit  = self::ROWS_PER_PAGE;
            $offset = ( $page - 1 ) * $limit;

                   if ($chkFilter)
                    {
                        list($count, $logs) = $this->getLog(
                            [
                            'account' => $_POST['account'],
                            'type' => $_POST['type'],
                            'paymentSystem' => $_POST['paySys'],
                            'dateMin' => $_POST['dateMin'],
                            'dateMax' => $_POST['dateMax'],
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