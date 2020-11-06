<?php

namespace Environment\Modules;


use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Modules\PayFilter as PayFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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

    function conExcel ()
    {
        $resault = file_get_contents('php://input');
        $resault = json_decode($resault, true);
        $account = $resault['account'] ?? null;
        $system = $resault['system'] ?? null;
        $dateMin = !empty($resault['dateMin'] ) ? $resault['dateMin'] .' 00:00:00' : date('Y-m-01 00:00:00');
        $dateMax = !empty($resault['dateMax']) ? $resault['dateMax'].' 23:59:59' : date('Y-m-d  23:59:59');

        if ($account && !$system && $dateMin && $dateMax) {

            $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"
WHERE "p"."Account" LIKE :f_account AND ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max)        
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                'f_account' => '%'.$account.'%',
                'f_d_min'  => $dateMin,
                'f_d_max'  => $dateMax,
            ] );

            $res =  $stmt->fetchAll();

        }
        if ($account && $system && $dateMin && $dateMax) {

            $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"
WHERE "p"."Account" LIKE :f_account AND ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem        
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                'f_account' => '%'.$account.'%',
                'f_paymentSystem'  => $system,
                'f_d_min'  => $dateMin,
                'f_d_max'  => $dateMax,
            ] );

            $res =  $stmt->fetchAll();

        }

        if (!$account && $system && $dateMin && $dateMax) {

            $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"
WHERE  ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem        
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                'f_paymentSystem'  => $system,
                'f_d_min'  => $dateMin,
                'f_d_max'  => $dateMax,
            ] );

            $res =  $stmt->fetchAll();

        }
        if (!$account && !$system && $dateMin && $dateMax) {

            $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"
WHERE  ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max)        
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                'f_d_min'  => $dateMin,
                'f_d_max'  => $dateMax,
            ] );

            $res =  $stmt->fetchAll();

        }
        if (!$account && !$system && !$dateMin && !$dateMax) {

            $sql = <<<SQL
SELECT "p".*, "ps"."Name"
FROM
    "Payment"."Payment" AS "p"
    INNER JOIN "Payment"."PaymentSystem" AS "ps" ON "p"."PaymentSystemID" = "ps"."IDPaymentSystem"
ORDER BY 
    "p"."IDPayment" DESC,
    "p"."PayDateTime"
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([] );

            $res =  $stmt->fetchAll();

        }

        $rb = [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'underline' => Font::UNDERLINE_DOUBLE,
                'strikethrough' => false,
                'color' => [
                    'rgb' => '228B22'
                ]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => '228B22'
                    ]
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1')->applyFromArray($rb);
        $sheet->getStyle('B1')->applyFromArray($rb);
        $sheet->getStyle('C1')->applyFromArray($rb);
        $sheet->getStyle('D1')->applyFromArray($rb);
        $sheet->getStyle('E1')->applyFromArray($rb);
        $sheet->getStyle('F1')->applyFromArray($rb);

        $sheet->setTitle('Платежи Сочи');
        $sheet->setCellValueByColumnAndRow(1, 1, 'Аккаунт');
        $sheet->setCellValueByColumnAndRow(2, 1, 'Дата');
        $sheet->setCellValueByColumnAndRow(3, 1, 'Система');
        $sheet->setCellValueByColumnAndRow(4, 1, 'Сумма');
        $sheet->setCellValueByColumnAndRow(5, 1, 'ID транзакции');
        $row = 2;
        foreach($res as $index => $rs) {
            $sheet->setCellValueExplicitByColumnAndRow(1, $row, $rs['Account'],  DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(2, $row, $rs['PayDateTime'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(3, $row, $rs['Name'], DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(4, $row, $rs['Sum']);
            $sheet->setCellValueExplicitByColumnAndRow(5, $row, $rs['TXNID'], DataType::TYPE_STRING);
            $row ++;
        }
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        $per = 'D'.$row;
        $sheet->setCellValue($per, "=SUM(D2:$per)" );
        $sheet->getStyle($per)->applyFromArray($rb);
        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        echo json_encode('data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.base64_encode($xlsData));
        exit();




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