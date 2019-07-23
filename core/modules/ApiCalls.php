<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ApiCalls extends \Environment\Core\Module {
	const
		RESULTSET_LIMIT_MIN = 5,
		RESULTSET_LIMIT_MAX = 5000,
		RESULTSET_LIMIT_DEFAULT = 100;

	const
		RESULTSET_ARGUMENTS_DELIMITER = '^';

	const
		RESULT_TYPE_SUCCESS = 1,
		RESULT_TYPE_EXCEPTION = 2;

	protected $config = [
		'template' => 'layouts/ApiCalls/Default.html'
	];

	protected function getSubscribers() {
		$sql = <<<SQL
SELECT
    "s-s"."IDSubscriber" as "id",
    "s-s"."Name" as "name"
FROM
    "Subscriber"."Subscriber" as "s-s"
ORDER BY
    2;
SQL;

		$stmt = Connections::getConnection( 'Api' )->prepare( $sql );

		$stmt->execute();

		return $stmt->fetchAll();
	}

	protected function getServiceMethods() {
		$sql = <<<SQL
SELECT
    "a-s"."IDService" as "service-id",
    "a-s"."Name" as "service-name",
    "a-sm"."IDServiceMethod" as "method-id",
    "a-sm"."Name" as "method-name"
FROM
    "Api"."Service" as "a-s"
        INNER JOIN "Api"."ServiceMethod" as "a-sm"
            ON "a-s"."IDService" = "a-sm"."ServiceID"
ORDER BY
    2, 4;
SQL;

		$stmt = Connections::getConnection( 'Api' )->prepare( $sql );

		$stmt->execute();

		$result  = [];
		$service = null;

		while ( $row = $stmt->fetch() ) {
			$service = $row['service-id'];

			if ( ! isset( $result[ $service ] ) ) {
				$result[ $service ] = [
					'id'      => $row['service-id'],
					'name'    => $row['service-name'],
					'methods' => []
				];
			}

			$result[ $service ]['methods'][] = [
				'id'   => $row['method-id'],
				'name' => $row['method-name']
			];
		}

		return array_values( $result );
	}

	protected function getCalls( array $filters, $limit ) {
		$params = [];

		$values = [
			'delimiter' => self::RESULTSET_ARGUMENTS_DELIMITER,
			'limit'     => $limit
		];

		if ( array_key_exists( 'subscriber-id', $filters ) ) {
			$params[] = '("s-s"."IDSubscriber" = :subscriberId)';

			$values['subscriberId'] = $filters['subscriber-id'];
		}

		if ( array_key_exists( 'service-id', $filters ) ) {
			$params[] = '("a-s"."IDService" = :serviceId)';

			$values['serviceId'] = $filters['service-id'];
		}

		if ( array_key_exists( 'service-method-id', $filters ) ) {
			$params[] = '("a-sm"."IDServiceMethod" = :serviceMethodId)';

			$values['serviceMethodId'] = $filters['service-method-id'];
		}

		if ( array_key_exists( 'period-from', $filters ) ) {
			$params[] = '("a-smc"."DateTime" >= :periodFrom)';

			$values['periodFrom'] = $filters['period-from'];
		}

		if ( array_key_exists( 'period-to', $filters ) ) {
			$params[] = '("a-smc"."DateTime" <= :periodTo)';

			$values['periodTo'] = $filters['period-to'];
		}

		if ( array_key_exists( 'result-type', $filters ) ) {
			switch ( $filters['result-type'] ) {
				case self::RESULT_TYPE_SUCCESS:
					$params[] = '("a-smcr"."IDServiceMethodCallResult" IS NOT NULL)';
					break;

				case self::RESULT_TYPE_EXCEPTION:
					$params[] = '("a-smce"."IDServiceMethodCallException" IS NOT NULL)';
					break;
			}
		}

		$params = $params ? 'WHERE ' . implode( ' AND ', $params ) : '';

		$sql = <<<SQL
SELECT
    "a-smc"."IDServiceMethodCall" as "id",
    "a-smc"."ServiceMethodCallID" as "parent-id",
    "s-s"."Name" as "subscriber",
    CONCAT_WS('::', "a-s"."Name", "a-sm"."Name" || '(...)') as "service-method",
    CASE
        WHEN "a-smcr"."IDServiceMethodCallResult" IS NOT NULL
            THEN TO_CHAR("a-smcr"."DateTime" - "a-smc"."DateTime", 'DD, HH24:MI:SS (US)')
        WHEN "a-smce"."IDServiceMethodCallException" IS NOT NULL
            THEN TO_CHAR("a-smce"."DateTime" - "a-smc"."DateTime", 'DD, HH24:MI:SS (US)')
        ELSE NULL
    END AS "duration",
    TO_CHAR("a-smc"."DateTime", 'DD.MM.YYYY HH24:MI:SS (US)') as "stamp",
    "a-smcr"."IDServiceMethodCallResult" IS NOT NULL as "is-success",
    "a-smce"."IDServiceMethodCallException" IS NOT NULL as "is-failure",
    (
        SELECT
            STRING_AGG(
                "a-smca"."Value",
                :delimiter ORDER BY "a-smca"."IDServiceMethodCallArg"
            )
        FROM
            "Api"."ServiceMethodCallArg" as "a-smca"
        WHERE
            ("a-smca"."ServiceMethodCallID" = "a-smc"."IDServiceMethodCall")
    ) as "arguments"
FROM
    "Api"."ServiceMethodCall" as "a-smc"
        INNER JOIN "Subscriber"."Subscriber" as "s-s"
            ON "a-smc"."SubscriberID" = "s-s"."IDSubscriber"
        INNER JOIN "Api"."ServiceMethod" as "a-sm"
            ON "a-smc"."ServiceMethodID" = "a-sm"."IDServiceMethod"
        INNER JOIN "Api"."Service" as "a-s"
            ON "a-sm"."ServiceID" = "a-s"."IDService"
        LEFT JOIN "Api"."ServiceMethodCallResult" as "a-smcr"
            ON "a-smc"."IDServiceMethodCall" = "a-smcr"."ServiceMethodCallID"
        LEFT JOIN "Api"."ServiceMethodCallException" as "a-smce"
            ON "a-smc"."IDServiceMethodCall" = "a-smce"."ServiceMethodCallID"
{$params}
ORDER BY
    "a-smc"."IDServiceMethodCall" DESC
LIMIT
    :limit;
SQL;

		$stmt = Connections::getConnection( 'Api' )->prepare( $sql );

		set_time_limit( 0 );

		$stmt->execute( $values );

		return $stmt;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-api-calls.css';

		$this->variables->errors = [];

		$limit = isset( $_GET['limit'] )
			? abs( (int) $_GET['limit'] )
			: self::RESULTSET_LIMIT_DEFAULT;

		if ( $limit > self::RESULTSET_LIMIT_MAX ) {
			$limit = self::RESULTSET_LIMIT_MAX;
		} elseif ( $limit < self::RESULTSET_LIMIT_MIN ) {
			$limit = self::RESULTSET_LIMIT_MIN;
		}

		$subscriber = empty( $_GET['subscriber'] )
			? null
			: abs( (int) $_GET['subscriber'] );

		$service = empty( $_GET['service'] )
			? null
			: abs( (int) $_GET['service'] );

		$method = empty( $_GET['method'] )
			? null
			: abs( (int) $_GET['method'] );

		$resultType = empty( $_GET['result-type'] )
			? null
			: abs( (int) $_GET['result-type'] );

		$periodFrom = empty( $_GET['period-from'] )
			? null
			: $_GET['period-from'];

		$periodTo = empty( $_GET['period-to'] )
			? null
			: $_GET['period-to'];

		$this->variables->cLimit      = $limit;
		$this->variables->cSubscriber = $subscriber;
		$this->variables->cService    = $service;
		$this->variables->cMethod     = $method;
		$this->variables->cResultType = $resultType;
		$this->variables->cPeriodFrom = $periodFrom;
		$this->variables->cPeriodTo   = $periodTo;

		$filters = [];

		if ( $subscriber ) {
			$filters['subscriber-id'] = $subscriber;
		}

		if ( $service ) {
			$filters['service-id'] = $service;
		}

		if ( $method ) {
			$filters['service-method-id'] = $method;
		}

		if ( $resultType ) {
			$filters['result-type'] = $resultType;
		}

		if ( $periodFrom ) {
			$filters['period-from'] = date( 'Y-m-d H:i:s', strtotime( $periodFrom ) );
		}

		if ( $periodTo ) {
			$filters['period-to'] = date( 'Y-m-d H:i:s', strtotime( $periodTo ) );
		}

		$this->variables->resultTypes = [
			[
				'id'   => self::RESULT_TYPE_SUCCESS,
				'name' => 'успех'
			],
			[
				'id'   => self::RESULT_TYPE_EXCEPTION,
				'name' => 'ошибка'
			]
		];

		try {
			$this->variables->subscribers = $this->getSubscribers();
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка систем-подписчиков.';
		}

		try {
			$this->variables->serviceMethods = $this->getServiceMethods();
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка служб и методов.';
		}

		try {
			$this->variables->calls = $this->getCalls( $filters, $limit );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении данных о вызововах.';
		}
	}
}

?>