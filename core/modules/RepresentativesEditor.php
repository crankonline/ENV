<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class RepresentativesEditor extends \Environment\Core\Module {
	const REGEX_INVALID_PASSPORT = '/[^A-Z0-9\-]+/i';

	protected $config = [
		'template' => 'layouts/RepresentativesEditor/Default.html'
	];

	protected function readPostData( array $mapping ) {
		$record = [];

		foreach ( $mapping as $key ) {
			$record[ $key ] = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
		}

		return $record;
	}

	protected function canProceed( array &$result ) {
		foreach ( $result as &$section ) {
			if ( $section ) {
				return false;
			}
		}

		return true;
	}

	protected function searchRepresentative( $series, $number ) {
		$sql = <<<SQL
SELECT
    "c-p"."IDPassport" as "passport-id",
    "c-p"."Series" as "passport-series",
    "c-p"."Number" as "passport-number",
    "c-p"."IssuingAuthority" as "passport-issuing-authority",
    TO_CHAR("c-p"."IssuingDate", 'DD.MM.YYYY') as "passport-issuing-date",
    "c-rp"."IDRepresentative" as "representative-id",
    "c-rp"."Surname" as "representative-surname",
    "c-rp"."Name" as "representative-name",
    "c-rp"."MiddleName" as "representative-middle-name",
    STRING_AGG("c-rq"."Inn", ',') as "company-inns",
    COUNT("c-rq"."IDRequisites") as "company-count",
    string_agg("c-rr"."Phone", ' | ' ) as "phone",
    STRING_AGG("c-rq"."FullName", ' \n') as "full-name",
--     ARRAY_AGG('[inn:''' || "c-rq"."Inn" || ''', name:''' || "c-rq"."FullName" || ''', phone:''' || "c-rr"."Phone" || ''']') as "company-inns2",
    json_object_agg("c-rq"."Inn",
                    json_build_object(
                        'idReqRep',"c-rr"."IDRequisitesRepresentative",
                        'idRep', "c-rp"."IDRepresentative",
                        'inn',"c-rq"."Inn",
                        'name',"c-rq"."Name",
                        'phone',"c-rr"."Phone")
        )as "company-inns2"

    
FROM
    "Common"."Passport" as "c-p"
        INNER JOIN "Common"."Representative" as "c-rp"
            ON "c-p"."IDPassport" = "c-rp"."PassportID"
        LEFT JOIN (
            "Common"."RequisitesRepresentative" as "c-rr"
                INNER JOIN "Common"."Requisites" as "c-rq"
                    ON "c-rr"."RequisitesID" = "c-rq"."IDRequisites"
            ) ON "c-rp"."IDRepresentative" = "c-rr"."RepresentativeID"
WHERE
    ("c-p"."SubscriberID" = 1)
    AND
    (
        ("c-rq"."IDRequisites" IS NULL)
        OR
        "c-rq"."IsActive"
    )
    AND
    ("c-p"."Series" = :pSeries)
    AND
    ("c-p"."Number" = :pNumber)
GROUP BY
    "c-p"."IDPassport", "c-rp"."IDRepresentative";
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute( [
			'pSeries' => $series,
			'pNumber' => $number
		] );

		return $stmt->fetch();
	}

	protected function searchSimilarRepresentatives( $surname, $name, $middleName ) {
		$sql = <<<SQL
SELECT
    "c-p"."IDPassport" as "passport-id",
    "c-p"."Series" as "passport-series",
    "c-p"."Number" as "passport-number",
    "c-rp"."Surname" as "representative-surname",
    "c-rp"."Name" as "representative-name",
    "c-rp"."MiddleName" as "representative-middle-name",
    COUNT("c-rq"."IDRequisites") as "company-count"
FROM
    "Common"."Passport" as "c-p"
        INNER JOIN "Common"."Representative" as "c-rp"
            ON "c-p"."IDPassport" = "c-rp"."PassportID"
        LEFT JOIN (
            "Common"."RequisitesRepresentative" as "c-rr"
                INNER JOIN "Common"."Requisites" as "c-rq"
                    ON "c-rr"."RequisitesID" = "c-rq"."IDRequisites"
            ) ON "c-rp"."IDRepresentative" = "c-rr"."RepresentativeID"
WHERE
    ("c-p"."SubscriberID" = 1)
    AND
    (
        ("c-rq"."IDRequisites" IS NULL)
        OR
        "c-rq"."IsActive"
    )
    AND
    (
        (
            ("c-rp"."Surname" = :surname)
            AND
            ("c-rp"."Name" = :name)
            AND
            (
                ("c-rp"."MiddleName" IS NULL)
                OR
                ("c-rp"."MiddleName" = :middleName)
            )
        )
        OR
        (
            ("c-rp"."Surname" = :surname)
            AND
            ("c-rp"."Name" = :name)
        )
        OR
        ("c-rp"."Surname" = :surname)
    )
GROUP BY
    "c-p"."IDPassport", "c-rp"."IDRepresentative"
SQL;

		$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

		$stmt->execute( [
			'surname'    => $surname,
			'name'       => $name,
			'middleName' => $middleName
		] );

		return $stmt->fetchAll();
	}

	protected function validatePassport( array $record ) {
		$e = [];

		if ( empty( $record['passport-id'] ) ) {
			$e['passport-id'] = '?????? ???????????????? ???? ????????????.';
		}

		if ( empty( $record['passport-series'] ) ) {
			$e['passport-series'] = '?????????? ???????????????? ???? ??????????????.';
		} elseif ( preg_match( self::REGEX_INVALID_PASSPORT, $record['passport-series'] ) ) {
			$e['passport-series'] = '?????????? ???????????????? ???????????????? ???????????????? ??????????????.';
		} elseif ( mb_strlen( $record['passport-series'], 'UTF-8' ) > 10 ) {
			$e['passport-series'] = '?????????? ?????????? ???????????????? ?????????????????? 10 ????????????????.';
		}

		if ( empty( $record['passport-number'] ) ) {
			$e['passport-number'] = '?????????? ???????????????? ???? ????????????.';
		} elseif ( preg_match( self::REGEX_INVALID_PASSPORT, $record['passport-number'] ) ) {
			$e['passport-number'] = '?????????? ???????????????? ???????????????? ???????????????? ??????????????.';
		} elseif ( mb_strlen( $record['passport-number'], 'UTF-8' ) > 15 ) {
			$e['passport-number'] = '?????????? ???????????? ???????????????? ?????????????????? 15 ????????????????.';
		}

		if ( empty( $record['passport-issuing-authority'] ) ) {
			$e['passport-issuing-authority'] = '?????????? ???????????? ???????????????? ???? ????????????.';
		} elseif ( mb_strlen( $record['passport-issuing-authority'], 'UTF-8' ) > 255 ) {
			$e['passport-issuing-authority'] = '?????????? ???????????????????????? ???????????? ???????????? ???????????????? ?????????????????? 255 ????????????????.';
		}

		$record['passport-issuing-date'] = strtotime( $record['passport-issuing-date'] );

		if ( empty( $record['passport-issuing-date'] ) ) {
			$e['passport-issuing-date'] = '???????? ???????????? ???????????????? ???? ??????????????, ???????? ?????????????? ?? ???????????????? ??????????????.';
		}

		$record['passport-issuing-date'] = date( 'Y-m-d', $record['passport-issuing-date'] );

		return $e;
	}

	protected function validateRepresentative( array $record ) {
		$e = [];

		if ( empty( $record['representative-id'] ) ) {
			$e['representative-id'] = '?????? ?????????????????????????? ???? ????????????.';
		}

		if ( empty( $record['representative-surname'] ) ) {
			$e['representative-surname'] = '?????????????? ?????????????????????????? ???? ??????????????.';
		} elseif ( mb_strlen( $record['representative-surname'], 'UTF-8' ) > 25 ) {
			$e['representative-surname'] = '?????????? ?????????????? ?????????????????????????? ?????????????????? 25 ????????????????.';
		}

		if ( empty( $record['representative-name'] ) ) {
			$e['representative-name'] = '?????? ?????????????????????????? ???? ??????????????.';
		} elseif ( mb_strlen( $record['representative-name'], 'UTF-8' ) > 20 ) {
			$e['representative-name'] = '?????????? ?????????? ?????????????????????????? ?????????????????? 20 ????????????????.';
		}

		if ( ! empty( $record['representative-middle-name'] ) ) {
			if ( mb_strlen( $record['representative-middle-name'], 'UTF-8' ) > 25 ) {
				$e['representative-middle-name'] = '?????????? ???????????????? ?????????????????????????? ?????????????????? 25 ????????????????.';
			}
		}

		return $e;
	}

	protected function update() {
		$passport = $this->readPostData( [
			'passport-id',
			'passport-series',
			'passport-number',
			'passport-issuing-authority',
			'passport-issuing-date'
		] );

		$representative = $this->readPostData( [
			'representative-id',
			'representative-surname',
			'representative-name',
			'representative-middle-name'
		] );

		$validations = (
			$this->validatePassport( $passport )
			+
			$this->validateRepresentative( $representative )
		);

		if ( $validations ) {
			$this->variables->result = false;
			$this->variables->status = '???????????????? ?????????????? ??????????????????????. ?????????????????? ?????????????????? ?? ?????????? ??????????.';

			$this->variables->validations = &$validations;
		} else {
			try {
				$isTransaction = false;

				$dbms = Connections::getConnection( 'Requisites' );

				$dbms->beginTransaction();

				$isTransaction = true;

				$sql = <<<SQL
UPDATE
    "Common"."Passport"
SET
    "Series"           = :pSeries,
    "Number"           = :pNumber,
    "IssuingDate"      = :pIssuingDate,
    "IssuingAuthority" = :pIssuingAuthority
WHERE
    ("SubscriberID" = 1)
    AND
    ("IDPassport" = :id);
SQL;

				$stmt = $dbms->prepare( $sql );

				$stmt->execute( [
					'pSeries'           => $passport['passport-series'],
					'pNumber'           => $passport['passport-number'],
					'pIssuingDate'      => $passport['passport-issuing-date'],
					'pIssuingAuthority' => $passport['passport-issuing-authority'],
					'id'                => $passport['passport-id']
				] );

				$sql = <<<SQL
UPDATE
    "Common"."Representative"
SET
    "Surname"    = :rSurname,
    "Name"       = :rName,
    "MiddleName" = :rMiddleName
WHERE
    ("PassportID" = :passportId)
    AND
    ("IDRepresentative" = :id);
SQL;

				$stmt = $dbms->prepare( $sql );

				$stmt->execute( [
					'rSurname'    => $representative['representative-surname'],
					'rName'       => $representative['representative-name'],
					'rMiddleName' => $representative['representative-middle-name'],
					'passportId'  => $passport['passport-id'],
					'id'          => $representative['representative-id']
				] );

				$dbms->commit();

				$this->variables->result = true;
				$this->variables->status = '???????????? ??????????????????.';
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				if ( $isTransaction ) {
					$dbms->rollBack();
				}

				$this->variables->result = false;
				$this->variables->status = $e->getMessage();
			}
		}
	}

	protected function remove() {
		if ( empty( $_POST['passport-id'] ) ) {
			$this->variables->result = false;
			$this->variables->status = '?????? ???????????????? ???? ????????????.';
		} else {
			try {
				$sql = <<<SQL
DELETE FROM
    "Common"."Passport"
WHERE
    ("SubscriberID" = 1)
    AND
    ("IDPassport" = :id);
SQL;

				$stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

				$stmt->execute( [
					'id' => $_POST['passport-id']
				] );

				$this->variables->result = true;
				$this->variables->status = '???????????? ??????????????.';
			} catch ( \Exception $e ) {
				\Sentry\captureException( $e );
				$this->variables->result = false;
				$this->variables->status = $e->getMessage();
			}
		}
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-representatives-editor.css';

		$this->variables->errors = [];

		$this->variables->cSeries = null;
		$this->variables->cNumber = null;

		if ( isset( $_GET['series'], $_GET['number'] ) ) {
			if ( $_POST ) {
				if ( isset( $_POST['update'] ) ) {
					$this->update();
				} elseif ( isset( $_POST['remove'] ) ) {
					$this->remove();
				}
			}

			$cSeries = $_GET['series'];
			$cNumber = $_GET['number'];

			$this->variables->cSeries = $cSeries;
			$this->variables->cNumber = $cNumber;

			try {
				$cRepresentative = $this->searchRepresentative( $cSeries, $cNumber );

				if ( $cRepresentative ) {
					$this->variables->similars = $this->searchSimilarRepresentatives(
						$cRepresentative['representative-surname'],
						$cRepresentative['representative-name'],
						$cRepresentative['representative-middle-name']
					);
				}
			} catch ( \Exception $e ) {
				\Sentry\captureException( $e );
				$this->variables->errors[] = $e->getMessage();
			}

			$this->variables->cRepresentative = &$cRepresentative;
		}
	}
}

?>