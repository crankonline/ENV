<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class AggregateRegionsSti extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/AggregateRegionsSti/Default.html'
	];

	protected function getData() {
		$sql = <<<SQL
SELECT
    "sti-rgn"."IDRegion" as "region-id",
    "sti-rgn"."Name" as "region-name",
    COUNT("c-rqst"."IDRequisites") as "clients-count"
FROM
    "Sti"."Region" as "sti-rgn"
        LEFT JOIN (
            "Sti"."Requisites" as "sti-rqst"
                INNER JOIN "Uid"."Uid" as "u-u"
                    ON "sti-rqst"."UidID" = "u-u"."IDUid"
                INNER JOIN "Common"."Requisites" as "c-rqst"
                    ON "u-u"."IDUid" = "c-rqst"."UidID"
        ) ON
            (
                (
                    ("sti-rgn"."IDRegion" = "sti-rqst"."DefaultRegionID")
                    OR
                    ("sti-rgn"."IDRegion" = "sti-rqst"."ReceiveRegionID")
                )
                AND
                "c-rqst"."IsActive"
                AND
                "sti-rqst"."IsActive"
                AND
                ("u-u"."SubscriberID" = 1)
            )
GROUP BY
    1
ORDER BY
    1;
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute();

		return $stmt->fetchAll();
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-aggregate-regions-sti.css';

		$this->variables->errors = [];

		try {
			$this->variables->data = $this->getData();
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>