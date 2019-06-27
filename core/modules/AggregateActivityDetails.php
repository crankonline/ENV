<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class AggregateActivityDetails extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/AggregateActivityDetails/Default.html'
    ];

    protected function getActivityInfo($id){
        $sql = <<<SQL
SELECT
    "c-avt"."IDActivity" as "activity-id",
    "c-avt"."Name" as "activity-name",
    "c-avt"."Gked" as "activity-gked",
    COUNT("c-rqst"."IDRequisites") as "clients-count"
FROM
    "Common"."Activity" as "c-avt"
        LEFT JOIN (
            "Common"."Requisites" as "c-rqst"
                INNER JOIN "Uid"."Uid" as "u-u"
                    ON "c-rqst"."UidID" = "u-u"."IDUid"
        ) ON
            ("u-u"."SubscriberID" = 1)
            AND
            ("c-avt"."IDActivity" = "c-rqst"."MainActivityID")
            AND
            "c-rqst"."IsActive"
WHERE
    ("c-avt"."IDActivity" = :id)
GROUP BY
    1;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    protected function getClientsByActivity($id){
        $sql = <<<SQL
SELECT
    "u-u"."Value" as "uid",
    "c-rqst"."Inn" as "inn",
    "c-rqst"."Name" as "name",
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
        INNER JOIN "Uid"."Uid" as "u-u"
            ON "c-rqst"."UidID" = "u-u"."IDUid"
        INNER JOIN "Common"."LegalFormCivilLegalStatus" as "c-lfcls"
            ON "c-rqst"."LegalFormCivilLegalStatusID" = "c-lfcls"."IDLegalFormCivilLegalStatus"
        INNER JOIN "Common"."LegalForm" as "c-lf"
            ON "c-lfcls"."LegalFormID" = "c-lf"."IDLegalForm"
WHERE
    ("u-u"."SubscriberID" = 1)
    AND
    "c-rqst"."IsActive"
    AND
    ("c-rqst"."MainActivityID" = :id)
ORDER BY
    3;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-aggregate-activities.css';

        $this->context->view = static::AK_AGGREGATE_ACTIVITIES;

        $this->variables->errors = [];

        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(empty($id)){
            $this->variables->errors[] = 'Вид деятельности не задан.';
        }

        try {
            $activity = $this->getActivityInfo($id);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
            return;
        }

        if(!$activity){
            $this->variables->errors[] = 'Вид деятельности не найден.';
            return;
        }

        try {
            $clients = $this->getClientsByActivity($id);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
            return;
        }

        $this->variables->activity = &$activity;
        $this->variables->clients  = &$clients;
    }
}
?>