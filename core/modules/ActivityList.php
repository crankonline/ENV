<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ActivityList extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/ActivityList/Default.html'
	];

	protected function getData() {
		$sql = <<<SQL
WITH RECURSIVE "sup" AS (
    SELECT
        "c-avt"."IDActivity" as "id",
        "c-avt"."ActivityID" as "activity-id",
        "c-avt"."Name" as "name",
        "c-avt"."Gked" as "gked",
        0 as "level"
    FROM
        "Common"."Activity" as "c-avt"
    WHERE
        ("c-avt"."ActivityID" IS NULL)
    UNION ALL
    SELECT
        "c-avt"."IDActivity",
        "c-avt"."ActivityID",
        "c-avt"."Name",
        "c-avt"."Gked",
        "sup"."level" + 1
    FROM
        "Common"."Activity" as "c-avt"
            INNER JOIN "sup"
                ON "c-avt"."ActivityID" = "sup"."id"
)
SELECT
    "sup".*,
    COUNT("c-rqst"."IDRequisites") as "clients-count"
FROM
    "sup"
        LEFT JOIN "Common"."Requisites" as "c-rqst"
            ON
                ("sup"."id" = "c-rqst"."MainActivityID")
                AND
                "c-rqst"."IsActive"
GROUP BY
    1, 2, 3, 4, 5
ORDER BY
    1, 2;
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute();

		return $stmt->fetchAll();
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-activity-list.css';

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