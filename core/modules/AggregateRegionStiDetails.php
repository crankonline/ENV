<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class AggregateRegionStiDetails extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/AggregateRegionStiDetails/Default.html'
	];

	protected function getRegionInfo( $id ) {
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
WHERE
    ("sti-rgn"."IDRegion" = :id)
GROUP BY
    1;
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute( [
			'id' => $id
		] );

		return $stmt->fetch();
	}

	protected function getClientsByRegion( $id ) {
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
    "Sti"."Requisites" as "sti-rqst"
        INNER JOIN "Uid"."Uid" as "u-u"
            ON "sti-rqst"."UidID" = "u-u"."IDUid"
        INNER JOIN "Common"."Requisites" as "c-rqst"
            ON "u-u"."IDUid" = "c-rqst"."UidID"
        INNER JOIN "Common"."LegalFormCivilLegalStatus" as "c-lfcls"
            ON "c-rqst"."LegalFormCivilLegalStatusID" = "c-lfcls"."IDLegalFormCivilLegalStatus"
        INNER JOIN "Common"."LegalForm" as "c-lf"
            ON "c-lfcls"."LegalFormID" = "c-lf"."IDLegalForm"
WHERE
    ("u-u"."SubscriberID" = 1)
    AND
    "c-rqst"."IsActive"
    AND
    "sti-rqst"."IsActive"
    AND
    (
        ("sti-rqst"."DefaultRegionID" = :id)
        OR
        ("sti-rqst"."ReceiveRegionID" = :id)
    )
ORDER BY
    3;
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute( [
			'id' => $id
		] );

		return $stmt->fetchAll();
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-aggregate-regions-sti.css';

		$this->context->view = static::AK_AGGREGATE_REGIONS_STI;

		$this->variables->errors = [];

		$id = isset( $_GET['id'] ) ? $_GET['id'] : null;

		if ( empty( $id ) ) {
			$this->variables->errors[] = 'УГНС не задано.';
		}

		try {
			$region = $this->getRegionInfo( $id );
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();

			return;
		}

		if ( ! $region ) {
			$this->variables->errors[] = 'УГНС не найдено.';

			return;
		}

		try {
			$clients = $this->getClientsByRegion( $id );
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();

			return;
		}

		$this->variables->region  = &$region;
		$this->variables->clients = &$clients;
	}
}

?>