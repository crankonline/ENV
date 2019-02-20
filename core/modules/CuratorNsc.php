<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class CuratorNsc extends \Environment\Core\Module {
    const
        PMS_CLEAR_PROCESSING = 'can-clear-processing';

    const
        DEPLOYMENT_ADDRESS = 'curator.stat.kg';

    const
        REPORT_STATUS_ACCEPTED = 10,
        REPORT_STATUS_DECLINED = 20;

    protected
        $config = [
            'template' => 'layouts/CuratorNsc/Default.html',
            'listen'   => 'action'
        ],
        $fileMap = [
            1 => 'fileshare/data/',
            2 => 'fileshare/rendered/',
            3 => 'fileshare/notes/'
        ];

    protected function getMonthName($number){
        switch($number){
            case 1: return 'Январь';
            case 2: return 'Февраль';
            case 3: return 'Март';
            case 4: return 'Апрель';
            case 5: return 'Май';
            case 6: return 'Июнь';
            case 7: return 'Июль';
            case 8: return 'Август';
            case 9: return 'Сентябрь';
            case 10: return 'Октябрь';
            case 11: return 'Ноябрь';
            case 12: return 'Декабрь';
        }
    }

    protected function getReportByUin($uin){
        $sql = <<<SQL
SELECT
    "r-rpt"."IDReport" as "id",
    "r-pi"."Inn" as "payer-inn",
    "r-pi"."Name" as "payer-name",
    "r-rsi"."Name" as "report-supplier-name",
    "r-f"."Code" as "form-code",
    "r-fv"."Number" as "form-version-number",
    "r-fv"."Title" as "form-version-title",
    "r-ft"."Name" as "form-type-name",
    "r-rgn"."IDRegion" as "region-id",
    "r-rgn"."Code" as "region-code",
    "r-rgn"."Name" as "region-name",
    "r-rp"."Month" as "report-period-month",
    "r-rp"."Quarter" as "report-period-quarter",
    "r-rp"."Year" as "report-period-year",
    "r-rpt"."Uin" as "uin",
    TO_CHAR("r-rpt"."ImportDateTime", 'DD.MM.YYYY HH24:MI:SS') as "import-date-time",
    TO_CHAR("r-rpt"."SubmitionDateTime", 'DD.MM.YYYY HH24:MI:SS') as "submition-date-time"
FROM
    "Reporting"."Report" as "r-rpt"
        INNER JOIN "Reporting"."PayerImported" as "r-pi"
            ON
                (
                    ("r-rpt"."PayerImportedID" IS NULL)
                    AND
                    ("r-rpt"."PayerID" = "r-pi"."PayerID")
                    AND
                    "r-pi"."IsActual"
                )
                OR
                ("r-rpt"."PayerImportedID" = "r-pi"."IDPayerImported")
        INNER JOIN "Reporting"."ReportSupplierImported" as "r-rsi"
            ON
                (
                    ("r-rpt"."ReportSupplierImportedID" IS NULL)
                    AND
                    ("r-rpt"."ReportSupplierID" = "r-rsi"."ReportSupplierID")
                    AND
                    "r-rsi"."IsActual"
                )
                OR
                ("r-rpt"."ReportSupplierImportedID" = "r-rsi"."IDReportSupplierImported")
        INNER JOIN "Reporting"."FormVersionFormType" as "r-fvft"
            ON "r-rpt"."FormVersionFormTypeID" = "r-fvft"."IDFormVersionFormType"
        INNER JOIN "Reporting"."FormVersion" as "r-fv"
            ON "r-fvft"."FormVersionID" = "r-fv"."IDFormVersion"
        INNER JOIN "Reporting"."FormType" as "r-ft"
            ON "r-fvft"."FormTypeID" = "r-ft"."IDFormType"
        INNER JOIN "Reporting"."Form" as "r-f"
            ON "r-fv"."FormID" = "r-f"."IDForm"
        INNER JOIN "Reporting"."Region" as "r-rgn"
            ON "r-pi"."RegionID" = "r-rgn"."IDRegion"
        INNER JOIN "Reporting"."ReportPeriod" as "r-rp"
            ON "r-rpt"."ReportPeriodID" = "r-rp"."IDReportPeriod"
WHERE
    ("r-rpt"."Uin" = :uin);
SQL;

        $stmt = Connections::getConnection('Nsc')->prepare($sql);

        $stmt->execute([
            'uin' => $uin
        ]);

        return $stmt->fetch();
    }

    protected function getProtocolByReportId($reportId){
        $sql = <<<SQL
SELECT
    "rp-rer"."IDRuleExecutionResult" as "rule-execution-result-id",
    "rp-t"."Name" as "trigger-name",
    "rp-t"."Description" as "trigger-description",
    "r-rs"."IDReportStatus" as "report-status-id",
    "r-rs"."Name" as "report-status-name",
    TO_CHAR("rp-rer"."DateTime", 'DD.MM.YYYY HH24:MI:SS') as "date-time",
    "rp-rer"."isFinish" as "is-finish",
    "rp-rer"."Result" as "result"
FROM
    "ReportProcessing"."RuleExecutionResult" as "rp-rer"
        INNER JOIN "ReportProcessing"."RuleExecution" as "rp-re"
            ON "rp-rer"."RuleExecutionID" = "rp-re"."IDRuleExecution"
        INNER JOIN "Reporting"."ReportStatus" as "r-rs"
            ON "rp-rer"."ReportStatusID" = "r-rs"."IDReportStatus"
        INNER JOIN "ReportProcessing"."Rule" as "rp-r"
            ON "rp-re"."RuleID" = "rp-r"."IDRule"
        INNER JOIN "ReportProcessing"."Trigger" as "rp-t"
            ON "rp-r"."TriggerID" = "rp-t"."IDTrigger"
WHERE
    ("rp-re"."ReportID" = :reportId)
    AND
    ("rp-rer"."ReportStatusID" IS NOT NULL)
ORDER BY
    "rp-re"."ReportID",
    "rp-r"."Order",
    "rp-rer"."DateTime";
SQL;

        $stmt = Connections::getConnection('Nsc')->prepare($sql);

        $stmt->execute([
            'reportId' => $reportId
        ]);

        return $stmt->fetchAll();
    }

    protected function getFilesByReportId($reportId){
        $sql = <<<SQL
SELECT
    "r-rf"."IDReportFile" as "id",
    "r-rft"."IDReportFileType" as "report-file-type-id",
    "r-rft"."Name" as "report-file-type-name",
    "r-rf"."Name" as "name",
    "r-rf"."Size" as "size",
    ENCODE("r-rf"."Hash", 'hex') as "hash"
FROM
    "Reporting"."ReportFile" as "r-rf"
        INNER JOIN "Reporting"."ReportFileType" as "r-rft"
            ON "r-rf"."ReportFileTypeID" = "r-rft"."IDReportFileType"
WHERE
    ("r-rf"."ReportID" = :reportId)
ORDER BY
    2,
    1;
SQL;

        $stmt = Connections::getConnection('Nsc')->prepare($sql);

        $stmt->execute([
            'reportId' => $reportId
        ]);

        return $stmt->fetchAll();
    }

    protected function clearProcessing($reportId){
        $sql = <<<SQL
DELETE FROM
    "ReportProcessing"."RuleExecution"
WHERE
    ("ReportID" = :reportId);
SQL;

        $stmt = Connections::getConnection('Nsc')->prepare($sql);

        return $stmt->execute([
            'reportId' => $reportId
        ]);
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-curator.css';

        $this->variables->errors = [];

        $canClearProcessing = $this->isPermitted(
            self::AK_CURATOR_NSC,
            self::PMS_CLEAR_PROCESSING
        );

        $uin = isset($_GET['uin']) ? $_GET['uin'] : null;

        $this->variables->cUin = $uin;

        $this->variables->canClearProcessing = $canClearProcessing;

        if(empty($uin)){
            return;
        }

        try {
            $report = $this->getReportByUin($uin);

            $this->variables->report = &$report;

            if($report){
                if($_POST){
                    $isClearProcessing = (
                        isset($_POST['clear-processing'])
                        &&
                        $canClearProcessing
                    );

                    if($isClearProcessing){
                        $result = $this->clearProcessing($report['id']);

                        $status = $result
                            ? 'Протокол проверки успешно очищен.'
                            : 'Не удалось очистить протокол проверки.';
                    } else {
                        $result = false;
                        $status = 'Неверный набор параметров для осуществления действия.';
                    }

                    $this->variables->result = $result;
                    $this->variables->status = $status;
                }

                $this->variables->protocol = $this->getProtocolByReportId(
                    $report['id']
                );

                $this->variables->files = $this->getFilesByReportId(
                    $report['id']
                );
            }
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>