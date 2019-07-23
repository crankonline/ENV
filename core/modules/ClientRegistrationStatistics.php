<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ClientRegistrationStatistics extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/ClientRegistrationStatistics/Default.html'
	];

	protected function getAuthorByIp( $ip ) {
		switch ( $ip ) {
			case '10.0.100.3':
				return 'Елена Петровна';
			case '172.16.1.3':
				return 'Разработчики ПО';
			case '192.168.1.12':
				return 'Салтанат';
			case '192.168.1.13':
				return 'Гульбара';
			case '192.168.1.16':
				return 'Елена Петровна';
			case '192.168.1.17':
				return 'Бектур';
			case '192.168.1.18':
				return 'Иванов Дмитрий';
			case '192.168.1.19':
				return 'Азиза';
			case '192.168.1.23':
				return 'Настя';
			case '192.168.1.29':
				return 'Гузаля';
			case '192.168.1.34':
				return 'Вадим';
			case '192.168.1.39':
				return 'Алина';
			case '192.168.1.40':
				return 'Света';
			case '192.168.1.138':
				return 'Аня';
			case '192.168.1.145':
				return 'Женя';
			case '192.168.1.154':
				return 'Cтепан';
			case '192.168.1.238':
				return 'Святослав';
			case '192.168.1.157':
				return 'Эльвира';
			case '192.168.1.201':
				return '#6';
		}

		return '?';
	}

	protected function getRecords( $periodFrom, $periodTo ) {
		$sql = <<<SQL
SELECT
    HOST("s-a"."IpAddress") as "ip-address",
    TO_CHAR("s-a"."DateTime", 'DD.MM.YYYY') as "date",
    SUM(("s-at"."Name" = 'register')::INT) as "register-count",
    SUM(("s-at"."Name" = 'update')::INT) as "update-count"
FROM
    "Statistics"."Action" as "s-a"
        INNER JOIN "Statistics"."ActionType" as "s-at"
            ON "s-a"."ActionTypeID" = "s-at"."IDActionType"
WHERE
    ("s-a"."DateTime"::DATE BETWEEN :periodFrom AND :periodTo)
GROUP BY
    1,
    2
ORDER BY
    2 DESC,
    3 DESC,
    4 DESC;
SQL;

		$stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

		$stmt->execute( [
			'periodFrom' => $periodFrom,
			'periodTo'   => $periodTo
		] );

		return $stmt;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-client-registration-statistics.css';

		$this->variables->errors = [];

		$periodFrom = isset( $_GET['period-from'] )
			? $_GET['period-from']
			: null;

		$periodTo = isset( $_GET['period-to'] )
			? $_GET['period-to']
			: date( 'Y-m-d' );

		if ( ! strtotime( $periodFrom ) ) {
			$periodFrom = date( 'Y-m-' ) . '01';
		}

		if ( ! strtotime( $periodTo ) ) {
			$periodTo = date( 'Y-m-d' );
		}

		$this->variables->periodFrom = $periodFrom;
		$this->variables->periodTo   = $periodTo;

		try {
			$this->variables->records = $this->getRecords( $periodFrom, $periodTo );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>