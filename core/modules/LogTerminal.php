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

                if ($filters['type']) {

                    $params[] = 'AND ("p"."Type" = :f_type)';

                } elseif ($filters['paymentSystem']) {

                    $params[] = 'AND ("p"."PaymentSystemID" = :f_paymentSystem)';

                } elseif ($filters['dateMin']) {

                    $params[] = 'AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';


                } else {
                    $params[0] = NULL;
                }

                $sql = <<<SQL
SELECT *
FROM
    "Payment"."Log" AS "p"
WHERE  "p"."Account" like '%{$filters['account']}%'
    {$params[0]}
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


                }  elseif ($filters['paymentSystem']) {

                    $stmt->execute([
                        'f_paymentSystem'   => $filters['paymentSystem']
                    ]);


                }  elseif ($filters['type']) {
                    $stmt->execute([
                        'f_type'   => $filters['type']
                    ]);


                }  elseif ($filters['type'] && $filters['paymentSystem']) {
                     $stmt->execute([
                        'f_type'   => $filters['type'],
                        'f_paymentSystem'   => $filters['paymentSystem']
                    ]);


                } elseif ($filters['type'] && $filters['dateMin']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));

                    $stmt->execute([
                        'f_type'   => $filters['type'],
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);


                } elseif ($filters['paymentSystem'] && $filters['dateMin']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));

                    $stmt->execute([
                        'f_paymentSystem'   => $filters['paymentSystem'],
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);

                } elseif ($filters['dateMin']) {
                    $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['dateMax'])));
                    $stmt->execute([
                        'f_d_min'  => $filters['dateMin'],
                        'f_d_max'  => $premium_date
                    ]);

                }

                else {

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
SELECT *
FROM
    "Payment"."Log" AS "p"
    
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
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-clients-list.css';
        $this->variables->mes = [];
        $this->variables->errors = [];
        $chkFilter =  $_GET['chkFilter'] ?? null;

        try {

            $this->variables->account          = $_POST['account'] ?? null;
            $this->variables->dateMin          = $_POST['dateMin'] ?? null;
            $this->variables->dateMax          = $_POST['dateMax'] ?? null;
            $this->variables->paymentSystem    = $_POST['paymentSystem'] ?? null;
            $this->variables->type             = $_POST['type'] ?? null;



            $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
            $limit  = self::ROWS_PER_PAGE;
            $offset = ( $page - 1 ) * $limit;

                   if ($chkFilter)
                    {
                        list($count, $logs) = $this->getLog(
                            [
                            'account' => $_POST['account'],
                            'type' => $_POST['type'],
                            'paymentSystem' => $_POST['paymentSystem'],
                            'dateMin' => $_POST['dateMin'],
                            'dateMax' => $_POST['dateMax'],
                            ],
                            $limit, $offset
                        );

                        if ($logs) {
                            $this->variables->logs = $logs;
                        } else {
                            $this->variables->mes[] = 'Не найдено записей';
                        }
                    } else {

                        list($count, $logs) = $this->getLog([], $limit, $offset);

                        $this->context->paginator['count'] = (int)ceil($count / $limit);

                        $this->variables->count = $count;
                        $this->variables->logs = $logs;
                    }


        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }
}