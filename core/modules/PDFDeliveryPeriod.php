<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class PDFDeliveryPeriod extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/PDFDeliveryPeriod/Default.html',
        'listen'   => 'action'
    ];

    private function getReportPeriod ($month, $quarter, $year) {
        if($month){
            $params[] = '("r"."Month" = :mo)';

            $values['mo'] = $month;

        }

        if ($quarter) {
            $params[] = '("r"."Quarter" = :qu)';

            $values['qu'] = $quarter;

        }

        if ($year) {
            $params[] = '("r"."Year" = :ye)';

            $values['ye'] = $year;

        }


        if(!$params){
            return false;
        }

        $params = 'WHERE ' . implode(' AND ', $params);


        $sql = <<<SQL
SELECT "r".* 
    FROM "Reporting"."ReportPeriod" AS "r"
    {$params}
SQL;

        $stmt = Connections::getConnection('Sti')->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();

    }

    private function updateReport($IDReportPeriod, $IDReport) {

        $sql = <<<SQL
UPDATE
    "Reporting"."Report"
SET
    "ReportPeriodID" = :idPer

WHERE
    ("IDReport" = :id);
SQL;

        $stmt = Connections::getConnection('Sti')->prepare($sql);

        echo json_encode($stmt->execute([
            'id' => $IDReport,
            'idPer' => $IDReportPeriod
        ])); exit();



    }

    public function getData() {
        try {


            if ($_POST) {
                $uin = $_POST['uin'] ?? null;
            $sql = <<<SQL
SELECT "Reporting"."ReportPeriod".*, "Reporting"."Report"."IDReport" 
    FROM "Reporting"."ReportPeriod" 
    INNER JOIN "Reporting"."Report" ON "IDReportPeriod" = "ReportPeriodID" 
    WHERE "Uin" = :uin
SQL;



                $stmt = Connections::getConnection('Sti')->prepare($sql);

                $stmt->execute(['uin' => $uin]);

                echo json_encode($stmt->fetchAll());
                exit();
            }

            } catch ( \Exception $e ) {
                \Sentry\captureException( $e );
                $this->variables->errors[] = 'Не найдено записей';
            }

    }

    public function setData() {
        try {

                $month = $_POST['month'] ?? null;
                $quarter = $_POST['quarter'] ?? null;
                $year = $_POST['year'] ?? null;
                $reportId = $_POST['reportId'] ?? null;

                $params = [];
                $values = [];
                $res = $this->getReportPeriod ($month, $quarter, $year);

                if ($res[0]['IDReportPeriod'] ) {

                    if ($reportId) {
                        $this->updateReport($res[0]['IDReportPeriod'], $reportId);

                    }
                } else {

                    if($month && $year){

                        $params[] = '("Month", "Year") VALUES (:mo, :ye)';

                        $values['mo'] = $month;
                        $values['ye'] = $year;

                    }

                    if ($quarter && $year) {
                        $params[] = '("Quarter", "Year") VALUES (:qu, :ye)';

                        $values['qu'] = $quarter;
                        $values['ye'] = $year;

                    }
                    $sql = <<<SQL
INSERT INTO
    "Reporting"."ReportPeriod"
{$params[0]}
    RETURNING
    "IDReportPeriod";



SQL;

                    $stmt = Connections::getConnection('Sti')->prepare($sql);

                    $stmt->execute($values);

                     $ins = $stmt->fetchAll();

                    $this->updateReport($ins[0]['IDReportPeriod'], $reportId);

                }



                echo json_encode('success');
                exit();

            } catch ( \Exception $e ) {
                \Sentry\captureException( $e );
                $this->variables->errors[] = 'Не найдено записей';
            }

    }

    protected function main() {
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-representatives-search.css';
        $this->variables->cUin = $_POST['uin'] ?? null;
    }
}

?>