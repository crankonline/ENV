<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class AggregateReports extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/AggregateReports/Default.html'
    ];

    protected function getActivities(){
        $sql = <<<SQL
SELECT
    "c-avt"."IDActivity" as "id",
    "c-avt"."Name" as "name",
    "c-avt"."Gked" as "gked",
    COUNT("c-rqst"."IDRequisites") as "clients-count"
FROM
    "Common"."Activity" as "c-avt"
        INNER JOIN "Common"."Requisites" as "c-rqst"
            ON "c-avt"."IDActivity" = "c-rqst"."MainActivityID"
        INNER JOIN "Uid"."Uid" as "u-u"
            ON "c-rqst"."UidID" = "u-u"."IDUid"
WHERE
    "c-rqst"."IsActive"
    AND
    ("u-u"."SubscriberID" = 1)
GROUP BY
    1
ORDER BY
    3,
    1;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function getLegalForms(){
        $sql = <<<SQL
SELECT
    "c-lf"."IDLegalForm" as "id",
    "c-lf"."Name" as "name",
    COUNT("c-rqst"."IDRequisites") as "clients-count"
FROM
    "Common"."LegalForm" as "c-lf"
        INNER JOIN "Common"."LegalFormCivilLegalStatus" as "c-lfcls"
            ON "c-lf"."IDLegalForm" = "c-lfcls"."LegalFormID"
        INNER JOIN "Common"."Requisites" as "c-rqst"
            ON "c-lfcls"."IDLegalFormCivilLegalStatus" = "c-rqst"."LegalFormCivilLegalStatusID"
        INNER JOIN "Uid"."Uid" as "u-u"
            ON "c-rqst"."UidID" = "u-u"."IDUid"
WHERE
    "c-rqst"."IsActive"
    AND
    ("u-u"."SubscriberID" = 1)
GROUP BY
    1
ORDER BY
    3 DESC,
    1;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function getUsers(array & $activities, array & $legalForms){
        $params = [];
        $values = [];

        $params[] = '"c-rqst"."IsActive"';
        $params[] = '("u-u"."SubscriberID" = 1)';

        if($activities){
            $count    = count($activities);
            $places   = implode(',', array_fill(0, $count, '?'));
            $params[] = '("c-avt"."IDActivity" IN (' . $places . '))';

            foreach($activities as $activity){
                $values[] = $activity;
            }
        }

        if($legalForms){
            $count    = count($legalForms);
            $places   = implode(',', array_fill(0, $count, '?'));
            $params[] = '("c-lf"."IDLegalForm" IN (' . $places . '))';

            foreach($legalForms as $legalForm){
                $values[] = $legalForm;
            }
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "u-u"."Value" as "uid",
    "c-rqst"."Inn" as "inn",
    CONCAT_WS(
        ' ',
        COALESCE(
            "c-lf"."ShortName",
            "c-lf"."Name"
        ),
        "c-rqst"."Name"
    ) as "name"
FROM
    "Common"."Requisites" as "c-rqst"
        INNER JOIN "Common"."LegalFormCivilLegalStatus" as "c-lfcls"
            ON "c-lfcls"."IDLegalFormCivilLegalStatus" = "c-rqst"."LegalFormCivilLegalStatusID"
        INNER JOIN "Common"."LegalForm" as "c-lf"
            ON "c-lfcls"."LegalFormID" = "c-lf"."IDLegalForm"
        INNER JOIN "Common"."Activity" as "c-avt"
            ON "c-rqst"."MainActivityID" = "c-avt"."IDActivity"
        INNER JOIN "Uid"."Uid" as "u-u"
            ON "c-rqst"."UidID" = "u-u"."IDUid"
{$params};
SQL;
        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    protected function getForms(){
        $sql = <<<SQL
SELECT
    3000 as "id",
    'СФ - Расчетная ведомость' as "name"
UNION ALL
SELECT
    *
FROM
    (
        SELECT
            "sti-frm"."id" + 1000,
            CONCAT_WS(' ', 'ГНС', '-', "sti-frm"."form_name")
        FROM
            "sti_reporting"."forms" as "sti-frm"
        WHERE
            "sti-frm"."status"
        ORDER BY
            2
    ) as "sub-sti"
UNION ALL
SELECT
    *
FROM
    (
        SELECT
            "stat-frm"."id" + 2000,
            CONCAT_WS(' ', 'НСК', '-', "stat-frm"."form_name")
        FROM
            "stat_reporting"."forms" as "stat-frm"
        WHERE
            "stat-frm"."status"
        ORDER BY
            2
    ) as "sub-nsc";
SQL;

        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }
/*
    protected function getReports(
        array & $users,
        array & $forms,
        $periodFrom,
        $periodTo
    ){
        $params = [
            'sf'  => [],
            'sti' => [],
            'nsc' => [],
            'sub' => []
        ];

        $values = [];

        $params['sf'][] = '("sf-rpt"."input_date" BETWEEN ? AND ?)';
        $values[] = $periodFrom;
        $values[] = $periodTo;

        $params['sti'][] = '("sti-rpt"."input_date" BETWEEN ? AND ?)';
        $values[] = $periodFrom;
        $values[] = $periodTo;

        $params['nsc'][] = '("nsc-rpt"."input_date" BETWEEN ? AND ?)';
        $values[] = $periodFrom;
        $values[] = $periodTo;

        if($users){
            $count           = count($users);
            $places          = implode(',', array_fill(0, $count, '?'));
            $params['sub'][] = '("sub"."uid" IN (' . $places . '))';

            foreach($users as &$user) {
                $values[] = $user['uid'];
            }
        }

        if($forms){
            $count           = count($forms);
            $places          = implode(',', array_fill(0, $count, '?'));
            $params['sub'][] = '("sub"."form" IN (' . $places . '))';

            foreach($forms as &$form) {
                $values[] = $form['id'];
            }
        }

        foreach($params as $key => $subset){
            $params[$key] = $params[$key]
                ? 'WHERE ' . implode(' AND ', $params[$key])
                : null;
        }

        $sql = <<<SQL
SELECT
    CONCAT_WS('-', "sub"."uid", "sub"."form") as "uid-form",
    "count" as "count"
FROM
    (
        SELECT
            "sf-rpt"."uid" as "uid",
            3000 as "form",
            COUNT(*) as "count"
        FROM
            "sf_reporting"."pass_reports" as "sf-rpt"
        {$params['sf']}
        GROUP BY
            1, 2
        UNION ALL
        SELECT
            "sti-rpt"."user_id",
            "sti-rpt"."form_id" + 1000,
            COUNT(*)
        FROM
            "sti_reporting"."reports" as "sti-rpt"
        {$params['sti']}
        GROUP BY
            1, 2
        UNION ALL
        SELECT
            "nsc-rpt"."uid",
            "nsc-rpt"."form_id" + 2000,
            COUNT(*)
        FROM
            "stat_reporting"."reports" as "nsc-rpt"
        {$params['nsc']}
        GROUP BY
            1, 2
    ) as "sub"
{$params['sub']};
SQL;

        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute($values);

        $result = $stmt->fetchAll();

        foreach($result as $index => $row){
            $result[$row['uid-form']] = $row;

            unset($result[$index]);
        }

        return $result;
    }
*/

    protected function getReports($periodFrom, $periodTo){
        $sql = <<<SQL
SELECT
    CONCAT_WS('-', "sf-rpt"."uid", 3000) as "uid-form",
    TO_CHAR("sf-rpt"."input_date", 'MM.YYYY') as "period",
    COUNT(*) as "count"
FROM
    "sf_reporting"."pass_reports" as "sf-rpt"
WHERE
    ("sf-rpt"."input_date" BETWEEN :periodFrom AND :periodTo)
GROUP BY
    1,
    2
UNION ALL
SELECT
    CONCAT_WS('-', "sti-rpt"."user_id", "sti-rpt"."form_id" + 1000),
    TO_CHAR("sti-rpt"."input_date", 'MM.YYYY'),
    COUNT(*)
FROM
    "sti_reporting"."reports" as "sti-rpt"
WHERE
    ("sti-rpt"."input_date" BETWEEN :periodFrom AND :periodTo)
GROUP BY
    1,
    2
UNION ALL
SELECT
    CONCAT_WS('-', "nsc-rpt"."uid", "nsc-rpt"."form_id" + 2000),
    TO_CHAR("nsc-rpt"."input_date", 'MM.YYYY'),
    COUNT(*)
FROM
    "stat_reporting"."reports" as "nsc-rpt"
WHERE
    ("nsc-rpt"."input_date" BETWEEN :periodFrom AND :periodTo)
GROUP BY
    1,
    2
ORDER BY
    2 DESC;
SQL;

        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute([
            'periodFrom' => $periodFrom,
            'periodTo'   => $periodTo
        ]);

        $result = $stmt->fetchAll();

        foreach($result as $index => $row){
            $group  = $row['uid-form'];
            $period = $row['period'];

            unset($row['uid-form'], $row['period'], $result[$index]);

            if(!isset($result[$group])){
                $result[$group] = [];
            }

            $result[$group][$period] = $row['count'];
        }

        return $result;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-aggregate-reports.css';

        $this->variables->errors = [];

        $this->context->title = null;

        $this->variables->cActivities = [];
        $this->variables->cLegalForms = [];
        $this->variables->cForms      = [];

        $this->variables->cPeriodFrom = date('Y-m-') . '01';
        $this->variables->cPeriodTo   = date('Y-m-d');

        try {
            $this->variables->activities = $this->getActivities();
        } catch(\PDOException $e) {
            $this->variables->errors[] = 'При получении списка видов деятельности произошла ошибка.';
            return;
        }

        try {
            $this->variables->legalForms = $this->getLegalForms();
        } catch(\PDOException $e) {
            $this->variables->errors[] = 'При получении списка организационно-правовых форм произошла ошибка.';
            return;
        }

        try {
            $this->variables->forms = $this->getForms();
        } catch(\PDOException $e) {
            $this->variables->errors[] = 'При получении списка форм отчетности произошла ошибка.';
            return;
        }

        if($_POST){
            $cActivities = empty($_POST['activities'])
                ? $this->variables->cActivities
                : $_POST['activities'];

            $cLegalForms = empty($_POST['legal-forms'])
                ? $this->variables->cLegalForms
                : $_POST['legal-forms'];

            $cForms = empty($_POST['forms'])
                ? $this->variables->cForms
                : $_POST['forms'];

            $cPeriodFrom = empty($_POST['from'])
                ? $this->variables->cPeriodFrom
                : $_POST['from'];

            $cPeriodTo = empty($_POST['to'])
                ? $this->variables->cPeriodTo
                : $_POST['to'];

            $this->variables->cActivities = &$cActivities;
            $this->variables->cLegalForms = &$cLegalForms;
            $this->variables->cForms      = &$cForms;
            $this->variables->cPeriodFrom = &$cPeriodFrom;
            $this->variables->cPeriodTo   = &$cPeriodTo;

            if(empty($cForms)){
                $this->variables->targetForms = &$this->variables->forms;
            } else {
                $targetForms = [];

                foreach($this->variables->forms as &$form){
                    if(in_array($form['id'], $cForms)){
                        $targetForms[] = $form;
                    }
                }

                $this->variables->targetForms = &$targetForms;
            }

            try {
                $this->variables->users = $this->getUsers($cActivities, $cLegalForms);
            } catch(\PDOException $e) {
                $this->variables->errors[] = 'При получении списка клиентов произошла ошибка.';
                return;
            }

            try {
/*
                $this->variables->reports = $this->getReports(
                    $this->variables->users,
                    $this->variables->targetForms,
                    $cPeriodFrom,
                    $cPeriodTo
                );
*/
                $this->variables->reports = $this->getReports($cPeriodFrom, $cPeriodTo);
            } catch(\PDOException $e) {
                $this->variables->errors[] = $e->getMessage(); // 'При получении данных об отчетности произошла ошибка';
            }

            set_time_limit(0);
        }
    }
}
?>