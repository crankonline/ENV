<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class CuratorSf extends \Environment\Core\Module {
    const
        DEPLOYMENT_ADDRESS = 'curator.sf.kg';

    const
        REPORT_STATUS_ACCEPTED    = 10,
        REPORT_STATUS_DECLINED    = 20,
        REPORT_STATUS_UNPROCESSED = 30;

    protected
        $config = [
            'template' => 'layouts/CuratorSf/Default.html',
            'listen'   => 'action'
        ],
        $fileMap = [
            1 => 'fileshare/data/',
            2 => 'fileshare/rendered/',
            3 => 'fileshare/notes/',
            4 => 'fileshare/images/',
            5 => 'fileshare/images/'
        ];

    protected function getReportByUin($uin){
        $sql = <<<SQL
SELECT
    "r-rpt"."IDReport" as "id",
    "r-pi"."Inn" as "payer-imported-inn",
    "r-pi"."Okpo" as "payer-imported-okpo",
    "r-pi"."Rnsf" as "payer-imported-rnsf",
    "r-pi"."Name" as "payer-imported-name",
    "r-pl"."Inn" as "payer-local-inn",
    "r-pl"."Name" as "payer-local-name",
    "r-pl"."Okpo" as "payer-local-okpo",
    "r-pl"."Rnsf" as "payer-local-rnsf",
    "r-rsi"."Name" as "report-supplier-name",
    "r-f"."Code" as "form-code",
    "r-fv"."Number" as "form-version-number",
    CONCAT_WS(
        ' - ',
        "r-rgn-pi"."Code",
        "r-rgn-pi"."Name"
    ) as "region-imported",
    CONCAT_WS(
        ' - ',
        "r-rgn-pl"."Code",
        "r-rgn-pl"."Name"
    ) as "region-local",
    "r-rpt"."Uin" as "uin",
    TO_CHAR(
        "r-rpt"."ImportDateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) as "import-date-time",
    TO_CHAR(
        "r-rpt"."SubmitionDateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) as "submition-date-time",
    TO_CHAR(
        "r-rpt"."ProcessDateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) as "process-date-time",
    CONCAT_WS(
        ' ',
        "c-c"."Surname",
        "c-c"."Name",
        "c-c"."MiddleName"
    ) as "process-curator",
    "r-rs"."Name" as "report-status-name",
    "r-rpt"."NotesText" as "process-notes"
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
        INNER JOIN "Reporting"."PayerLocal" as "r-pl"
            ON
                (
                    ("r-rpt"."PayerLocalID" IS NULL)
                    AND
                    ("r-rpt"."PayerID" = "r-pl"."PayerID")
                    AND
                    ("r-pl"."IsActual")
                )
                OR
                ("r-rpt"."PayerLocalID" = "r-pl"."IDPayerLocal")
        INNER JOIN "Reporting"."ReportSupplierImported" as "r-rsi"
            ON
                (
                    ("r-rpt"."ReportSupplierImportedID" IS NULL)
                    AND
                    ("r-rpt"."ReportSupplierID" = "r-rsi"."ReportSupplierID")
                    AND
                    ("r-rsi"."IsActual")
                )
                OR
                ("r-rpt"."ReportSupplierImportedID" = "r-rsi"."IDReportSupplierImported")
        INNER JOIN "Reporting"."Region" as "r-rgn-pl"
            ON "r-pl"."RegionID" = "r-rgn-pl"."IDRegion"
        INNER JOIN "Reporting"."Region" as "r-rgn-pi"
            ON "r-pi"."RegionID" = "r-rgn-pi"."IDRegion"
        INNER JOIN "Reporting"."FormVersion" as "r-fv"
            ON "r-rpt"."FormVersionID" = "r-fv"."IDFormVersion"
        INNER JOIN "Reporting"."Form" as "r-f"
            ON "r-fv"."FormID" = "r-f"."IDForm"
        INNER JOIN "Reporting"."ReportStatus" as "r-rs"
            ON "r-rpt"."ReportStatusID" = "r-rs"."IDReportStatus"
        LEFT JOIN "Core"."Curator" as "c-c"
            ON "r-rpt"."CuratorID" = "c-c"."IDCurator"
WHERE
    ("r-rpt"."Uin" = :uin);
SQL;

        $stmt = Connections::getConnection('Sf')->prepare($sql);

        $stmt->execute([
            'uin' => $uin
        ]);

        return $stmt->fetch();
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

        $stmt = Connections::getConnection('Sf')->prepare($sql);

        $stmt->execute([
            'reportId' => $reportId
        ]);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-curator.css';

        $this->variables->errors = [];

        $uin = isset($_GET['uin']) ? $_GET['uin'] : null;

        $this->variables->cUin = $uin;

        if(empty($uin)){
            return;
        }

        try {
            $report = $this->getReportByUin($uin);

            $this->variables->report = &$report;

            if($report){
                $this->variables->files = $this->getFilesByReportId(
                    $report['id']
                );
            }
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>