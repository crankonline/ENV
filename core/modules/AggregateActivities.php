<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class AggregateActivities extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/AggregateActivities/Default.html'
    ];

    protected function getData(){
        $sql = <<<SQL
SELECT
    "c-avt"."IDActivity" as "activity-id",
    "c-avt"."Name" as "activity-name",
    "c-avt"."Gked" as "activity-gked",
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
    4 DESC,
    1;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-aggregate-activities.css';

        $this->variables->errors = [];

        try {
            $this->variables->data = $this->getData();
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>