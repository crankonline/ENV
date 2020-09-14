<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class Sochi extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/Sochi/Default.html',
		'listen'   => 'action'
	];

	protected function getMonthName( $number ) {
		switch ( $number ) {
			case 1:
				return 'Январь';
			case 2:
				return 'Февраль';
			case 3:
				return 'Март';
			case 4:
				return 'Апрель';
			case 5:
				return 'Май';
			case 6:
				return 'Июнь';
			case 7:
				return 'Июль';
			case 8:
				return 'Август';
			case 9:
				return 'Сентябрь';
			case 10:
				return 'Октябрь';
			case 11:
				return 'Ноябрь';
			case 12:
				return 'Декабрь';
		}

		return null;
	}

	public function getUser( $inn, $uid ) {
		$params = [];
		$values = [];

		if ( $uid ) {
			$params[] = '("b-u"."uid" = :uid)';

			$values['uid'] = $uid;
		} elseif ( $inn ) {
			$params[] = '("b-u"."inn" = :inn)';

			$values['inn'] = $inn;
		}

		$params = $params ? 'WHERE ' . implode( ' AND ', $params ) : '';

		$sql = <<<SQL
SELECT
    "b-u"."id" as "id",
    "b-u"."uid" as "uid",
    "b-u"."name" as "name",
    "b-u"."inn" as "inn",
    "b-u"."okpo" as "okpo"
FROM
    "billing"."users" as "b-u"
{$params}
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( $values );

		return $stmt->fetch();
	}

	public function getAccruals( $inn ) {
		$sql = <<<SQL
SELECT
    "b-s"."name" as "name",
    TO_CHAR("b-a"."date_time", 'DD.MM.YYYY HH24:MI:SS') as "date-time",
    "b-a"."amount" as "amount"
FROM
    "billing"."accrual" as "b-a"
        INNER JOIN "billing"."subscriber" as "b-s"
            ON "b-a"."subscriber_id" = "b-s"."id"
        INNER JOIN "billing"."client" as "b-c"
            ON "b-a"."client_id" = "b-c"."id"
WHERE
    ("b-c"."inn" = :inn)
ORDER BY
    "b-a"."date_time" DESC;
SQL;

		$stmt = Connections::getConnection( 'Billing' )->prepare( $sql );

		$stmt->execute( [
			'inn' => $inn
		] );

		return $stmt->fetchAll();
	}

	public function getBills( $inn ) {
		$sql = <<<SQL
SELECT
    "b-sc"."name" as "subscriber",
    "b-sv"."name" as "service",
    "b-sv"."code" as "code",
    TO_CHAR("b-b"."date_time", 'DD.MM.YYYY HH24:MI:SS') as "date-time",
    "b-b"."amount" as "amount"
FROM
    "billing"."bill" as "b-b"
        INNER JOIN "billing"."service" as "b-sv"
            ON "b-b"."service_id" = "b-sv"."id"
        INNER JOIN "billing"."subscriber" as "b-sc"
            ON "b-sv"."subscriber_id" = "b-sc"."id"
        INNER JOIN "billing"."client" as "b-c"
            ON "b-b"."client_id" = "b-c"."id"
WHERE
    ("b-c"."inn" = :inn)
ORDER BY
    "b-b"."date_time" DESC;
SQL;

		$stmt = Connections::getConnection( 'Billing' )->prepare( $sql );

		$stmt->execute( [
			'inn' => $inn
		] );

		return $stmt->fetchAll();
	}

	public function getBalance( $inn ) {
		$sql = <<<SQL
SELECT
    COALESCE("b-a"."payed", 0) - COALESCE("b-b"."wasted", 0)
FROM
    "billing"."client" as "b-c"
        LEFT JOIN (
            SELECT
                "b-b"."client_id",
                SUM("b-b"."amount") as "wasted"
            FROM
                "billing"."bill" as "b-b"
            WHERE 
                "b-b"."status" IN (1,2)
            GROUP BY
                1
        ) as "b-b"
            ON "b-b"."client_id" = "b-c"."id"
        LEFT JOIN (
            SELECT
                "b-a"."client_id",
                SUM("b-a"."amount") as "payed"
            FROM
                "billing"."accrual" as "b-a"
            WHERE 
                "b-a"."status" IN (1,2)
            GROUP BY
                1
        ) as "b-a"
            ON "b-a"."client_id" = "b-c"."id"
WHERE
    ("b-c"."inn" = :inn)
SQL;

		$stmt = Connections::getConnection( 'Billing' )->prepare( $sql );

		$stmt->execute( [
			'inn' => $inn
		] );

		return $stmt->fetchColumn();
	}

	public function getSfReports( $inn, $uid ) {
		$sql = <<<SQL
SELECT
    "r"."uin" as "uin",
    TO_CHAR("r"."input_date", 'DD.MM.YYYY HH24:MI:SS') as "input-date-time",
    "r"."period_month" as "period-month",
    "r"."period_year"  as "period-year",
    "s"."id" as "status-id",
    "s"."name" as "status",
    "sfrp".code as "code",
    "sfrp".name as "region"
    
FROM
    "sf_reporting"."pass_reports" as "r"
        INNER JOIN (
            SELECT
                5 as "id",
                'Ожидает проверки в К/П' as "name"
            UNION ALL
            SELECT
                6,
                'Ожидает доставки в К/П'
            UNION ALL
            SELECT
                7,
                'Ожидает оплаты'
            UNION ALL
            SELECT
                8,
                'Не доставлен в связи с истечением срока представления'
            UNION ALL
            SELECT
                9,
                'Отправка отменена'
            UNION ALL
            SELECT
                3,
                'Отклонен'
            UNION ALL
            SELECT
                4,
                'Принят'
        ) as "s"
            ON ("r"."status" = "s"."id")
        INNER JOIN "sf_reporting"."salary" as "sfrp"
            ON ("r"."region" = "sfrp"."code")

WHERE
    ("r"."uid" = :uid)
    OR
    (
        ("r"."uid" IS NULL)
        AND
        ("r"."inn" = :inn)
    )
ORDER BY
    "r"."input_date" DESC;
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( [
			'uid' => $uid,
			'inn' => $inn
		] );

		return $stmt->fetchAll();
	}

	public function getStiReports( $inn, $uid ) {
		$sql = <<<SQL
SELECT
    "f"."form_name" as "form",
    "f"."sys_name" as "form-sys-name",
    COALESCE("t"."name", '-') as "type",
    TO_CHAR("r"."input_date", 'DD.MM.YYYY HH24:MI:SS') as "input-date-time",
    "r"."uin" as "uin",
    "r"."period_month" as "period-month",
    "r"."period_quarter" as "period-quarter",
    "r"."period_year"  as "period-year",
    "rgn"."code" as "region-code",
    "rgn"."name" as "region-name",
    "s"."id" as "status-id",
    "s"."name" as "status"
FROM
    "sti_reporting"."reports" as "r"
        INNER JOIN "sti_reporting"."forms" as "f"
            ON ("r"."form_id" = "f"."id")
        INNER JOIN "sti_reporting"."region" as "rgn"
            ON ("r"."region_code" = "rgn"."code")
        LEFT JOIN (
            SELECT
                0 as "id",
                'Первоначальный' as "name"
            UNION ALL
            SELECT
                1,
                'Уточненный'
            UNION ALL
            SELECT
                2,
                'Дополнительный'
            UNION ALL
            SELECT
                3,
                'Ликвидационнный'
            UNION ALL
            SELECT
                4,
                'Промежуточный'
        ) as "t"
            ON ("r"."form_type" = "t"."id")
        LEFT JOIN (
            SELECT
                5 as "id",
                'Ожидает проверки в К/П' as "name"
            UNION ALL
            SELECT
                6,
                'Ожидает доставки в К/П'
            UNION ALL
            SELECT
                7,
                'Ожидает оплаты'
            UNION ALL
            SELECT
                8,
                'Не доставлен в связи с истечением срока представления'
            UNION ALL
            SELECT
                9,
                'Отправка отменена'
            UNION ALL
            SELECT
                3,
                'Отклонен'
            UNION ALL
            SELECT
                4,
                'Принят'
        ) as "s"
            ON ("r"."status" = "s"."id")
WHERE
    ("r"."user_id" = :uid)
    OR
    (
        ("r"."user_id" IS NULL)
        AND
        ("r"."inn" = :inn)
    )
ORDER BY
    "r"."input_date" DESC,
    "f"."form_name";
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( [
			'uid' => $uid,
			'inn' => $inn
		] );

		return $stmt->fetchAll();
	}

	public function getNscReports( $uid ) {
		$sql = <<<SQL
SELECT
    "f"."form_name" as "form",
    "f"."sys_name" as "form-sys-name",    
    TO_CHAR("r"."input_date", 'DD.MM.YYYY HH24:MI:SS') as "input-date-time",
    "r"."uin" as "uin",
    "r"."period_month" as "period-month",
    "r"."period_quarter" as "period-quarter",
    "r"."period_year"  as "period-year",
    "s"."id" as "status-id",
    "s"."name" as "status"
FROM
    "stat_reporting"."reports" as "r"
        INNER JOIN "stat_reporting"."forms" as "f"
            ON ("r"."form_id" = "f"."id")
        LEFT JOIN (
            SELECT
                5 as "id",
                'Ожидает проверки в К/П' as "name"
            UNION ALL
            SELECT
                6,
                'Ожидает доставки в К/П'
            UNION ALL
            SELECT
                7,
                'Ожидает оплаты'
            UNION ALL
            SELECT
                8,
                'Не доставлен в связи с истечением срока представления'
            UNION ALL
            SELECT
                9,
                'Отправка отменена'
            UNION ALL
            SELECT
                3,
                'Отклонен'
            UNION ALL
            SELECT
                4,
                'Принят'
        ) as "s"
            ON ("r"."status" = "s"."id")
WHERE
    ("r"."uid" = :uid)
ORDER BY
    "r"."input_date" DESC,
    "f"."form_name";
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( [
			'uid' => $uid
		] );

		return $stmt->fetchAll();
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-sochi.css';

		$this->variables->errors = [];

		$inn = isset( $_GET['inn'] ) ? $_GET['inn'] : null;
		$uid = isset( $_GET['uid'] ) ? $_GET['uid'] : null;

		if ( ! ( $inn || $uid ) ) {
			return;
		}

		if ( $inn && ! preg_match( '/^(\d{10,10})|(\d{14,14})$/', $inn ) ) {
			$this->variables->errors[] = 'ИНН должен состоять из 10 или 14 цифр';

			return;
		}

		if ( $uid && ! preg_match( '/^\d{23,23}$/', $uid ) ) {
			$this->variables->errors[] = 'UID должен состоять из 23 цифр';

			return;
		}

		try {
			$user = $this->getUser( $inn, $uid );

			if ( ! $user ) {
				throw new \Exception( 'Пользователь не найден' );
			}

			$this->variables->user = &$user;

			$this->variables->accruals = $this->getAccruals( $user['inn'] );
			$this->variables->bills    = $this->getBills( $user['inn'] );
			$this->variables->balance  = $this->getBalance( $user['inn'] );

			$this->variables->sfReports = $this->getSfReports(
				$user['inn'],
				$user['uid']
			);

			$this->variables->stiReports = $this->getStiReports(
				$user['inn'],
				$user['uid']
			);

			$this->variables->nscReports = $this->getNscReports(
				$user['uid']
			);
		} catch ( \Exception $e ) {
//			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>