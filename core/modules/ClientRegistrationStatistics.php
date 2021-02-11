<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ClientRegistrationStatistics extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/ClientRegistrationStatistics/Default.html'
	];

	public function getOperatorName($userId) {
        $sql = <<<SQL
SELECT
    "c-u"."Login"
FROM
    "Core"."User" as "c-u"      
WHERE
    ("c-u"."IDUser" = :userId);
SQL;

        $stmt = Connections::getConnection( 'Environment' )->prepare( $sql );

        $stmt->execute( [
            'userId' => $userId,
        ] );
        $ret = $stmt->fetch();
//        var_dump($ret);
//        die();
        if($ret) {
            return $ret['Login'];
        } else {
            return '-';
        }
    }

	public function getRecords( $periodFrom, $periodTo ) {
		$sql = <<<SQL
SELECT
    HOST("s-a"."IpAddress") as "ip-address",
    TO_CHAR("s-a"."DateTime", 'DD.MM.YYYY') as "date",
    "s-a"."UserID" as "userId",
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
    2,
    3
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
