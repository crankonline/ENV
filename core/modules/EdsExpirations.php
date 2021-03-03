<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class EdsExpirations extends \Environment\Core\Module {
	const
		EXPIRATION_INTERVAL_WEEK = 604800,  // 60 * 60 * 24 * 7
		EXPIRATION_INTERVAL_MONTH = 2592000; // 60 * 60 * 24 * 30

	const
		MODE_EXPIRING_WEEK = 1,
		MODE_EXPIRING_MONTH = 2,
		MODE_EXPIRED = 3;

	const
		ROLE_CHIEF = 1,
		ROLE_ACCOUNTANT = 2;

	protected $config = [
		'template' => 'layouts/EdsExpirations/Default.html',
		'listen'   => 'action'
	];

	protected function roleCodeToString( $role ) {
		switch ( $role ) {
			case self::ROLE_CHIEF:
				return 'Руководитель';

			case self::ROLE_ACCOUNTANT:
				return 'Бухгалтер';
		}


		return 'Неизвестно';
	}

	protected function getUsageStatuses( $certificates ) {
		$values = [];

		foreach ( $certificates as $certificate ) {
			$values[] = $certificate->Inn;
		}

		$values = array_values( array_unique( $values ) );
		$places = implode( ',', array_fill( 0, count( $values ), '?' ) );

		$sql = <<<SQL
SELECT
    "c-rqst"."Inn" as "inn",
    "u-s"."isActive" as "is-active"
FROM
    "Common"."Requisites" as "c-rqst"
        INNER JOIN "Uid"."Uid" as "u-uid"
            ON "c-rqst"."UidID" = "u-uid"."IDUid"
        INNER JOIN (
            "Uid"."UsageStatus" as "u-s"
                INNER JOIN (
                        SELECT
                            "UidID" as "uid-id",
                            MAX("IDUsageStatus") as "usage-status-id"
                        FROM
                            "Uid"."UsageStatus" as "u-s"
                        GROUP BY
                            1
                        ORDER BY
                            1 DESC
                    ) as "u-s-grouper" ON
                        ("u-s"."UidID" = "u-s-grouper"."uid-id")
                        AND
                        ("u-s"."IDUsageStatus" = "u-s-grouper"."usage-status-id")
            ) ON
                ("u-s"."UidID" = "u-s-grouper"."uid-id")
                AND
                ("u-s"."UidID" = "u-uid"."IDUid")
                AND
                ("u-s"."IDUsageStatus" = "u-s-grouper"."usage-status-id")
WHERE
    ("u-uid"."SubscriberID" = 1)
    AND
    "c-rqst"."IsActive"
    AND
    ("c-rqst"."Inn" IN ({$places}));
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute( $values );

		$result = [];

		foreach ( $stmt as $row ) {
			$result[ $row['inn'] ] = $row['is-active'];
		}

		return $result;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-eds-expirations.css';

		$this->variables->errors = [];

		$this->variables->modes = [
			[
				'id'   => self::MODE_EXPIRING_WEEK,
				'name' => 'Сертификаты, действие которых истечет в течение 7 дней'
			],
			[
				'id'   => self::MODE_EXPIRING_MONTH,
				'name' => 'Сертификаты, действие которых истечет в течение 30 дней'
			],
			[
				'id'   => self::MODE_EXPIRED,
				'name' => 'Сертификаты, действие которых истекло на данный момент'
			]
		];

		$this->variables->cMode = null;

		$mode = isset( $_GET['mode'] ) ? abs( (int) $_GET['mode'] ) : self::MODE_EXPIRED;

		$this->variables->cMode = $mode;

		try {
			$client = new SoapClients\PkiService();

			switch ( $mode ) {
				case self::MODE_EXPIRED:
					$certificates = $client->getExpiredEds();
					break;

				case self::MODE_EXPIRING_WEEK:
					$certificates = $client->getExpiringEds( self::EXPIRATION_INTERVAL_WEEK );
					break;

				case self::MODE_EXPIRING_MONTH:
					$certificates = $client->getExpiringEds( self::EXPIRATION_INTERVAL_MONTH );
					break;

				default:
					$certificates = [];
					break;
			}

			if(!empty($_GET['inn'])) {
				$certificates = array_filter($certificates, function($v) {
					return $v->Inn == $_GET['inn'];
				});
			}

			$_GET['all'] = $_GET['all'] ?? 0;

			if(empty($_GET['inn']) && $_GET['all'] == 0) {
				$certificates = array_slice($certificates, 0, 50);
			}

			$statuses = $certificates ? $this->getUsageStatuses( $certificates ) : [];

			$this->variables->certificates = &$certificates;
			$this->variables->statuses     = &$statuses;
		} catch ( \SoapFault $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->faultstring;
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>