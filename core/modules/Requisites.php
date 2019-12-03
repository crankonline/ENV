<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class Requisites extends \Environment\Core\Module {
	const
		ROLES_CHIEF = 1,
		ROLES_CONSULTING = 5,
		ROLES_ROOT = 6;

	const
		PMS_CAN_CHANGE_USAGE_STATUS = 'can-change-usage-status';

	protected $config = [
		'template' => 'layouts/Requisites/Default.html'
	];

	protected function getRequisites( $inn, $uid ) {
		$client = new SoapClients\Api\RequisitesData();

		$requisites = $uid
			? $client->getByUid( $client::SUBSCRIBER_TOKEN, $uid, null )
			: $client->getByInn( $client::SUBSCRIBER_TOKEN, $inn, null );

		if ( ! ( $requisites && $requisites->common ) ) {
			throw new \Exception( 'Клиент не найден' );
		}

		$consulting = null;

		foreach ( $requisites->common->representatives as $rep ) {
			foreach ( $rep->roles as $role ) {
				switch ( $role->id ) {
					case self::ROLES_CONSULTING:
						$consulting = $rep->person->passport;
						break 2;

					case self::ROLES_ROOT:
						$consulting = $rep->person->passport;
						break 2;
				}
			}
		}

		$bindings = $consulting
			? $client->getConsultingBindingsByPassport(
				$client::SUBSCRIBER_TOKEN,
				$consulting->series,
				$consulting->number
			)
			: null;

		return [ $requisites, $bindings ];
	}

	protected function getSochiBillingBalance( $inn ) {
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

	protected function getPkiCertificates( $inn ) {
		return ( new SoapClients\PkiService() )->search( $inn );
	}

    protected function getDateFromRequisites( $inn ) {
        $sql = <<<SQL
        
        SELECT
    "Requisites"."DateTime",
    "Requisites"."IDRequisites",
    "RequisitesRepresentative"."EdsUsageModelID",
    "RequisitesRepresentative"."RepresentativeID",
    "RequisitesRepresentativeRole"."RepresentativeRoleID",
    "EdsUsageModel"."Name"  as "EdsName",
    "RepresentativeRole"."Name" as "RoleName",
    "Passport"."Series",
    "Passport"."Number"
    
FROM
    "Common"."RequisitesRepresentativeRole"
        INNER JOIN
    "Common"."Representative"
    ON
            "RequisitesRepresentativeRole"."RepresentativeID" = "Representative"."IDRepresentative"
        INNER JOIN
    "Common"."RequisitesRepresentative"
    ON
            "Representative"."IDRepresentative" = "RequisitesRepresentative"."RepresentativeID"
        INNER JOIN
    "Common"."Requisites"
    ON
                "RequisitesRepresentative"."RequisitesID" = "Requisites"."IDRequisites" AND
                "RequisitesRepresentativeRole"."RequisitesID" = "Requisites"."IDRequisites"
        INNER JOIN
    "Common"."EdsUsageModel"
    ON
            "RequisitesRepresentative"."EdsUsageModelID" = "EdsUsageModel"."IDEdsUsageModel"
        INNER JOIN
    "Common"."RepresentativeRole"
    ON
            "RequisitesRepresentativeRole"."RepresentativeRoleID" = "RepresentativeRole"."IDRepresentativeRole"
        INNER JOIN
    "Common"."Passport"
    ON
            "Representative"."PassportID" = "Passport"."IDPassport"
WHERE
      ("Requisites"."Inn" = :inn)
SQL;


        $stmt = Connections::getConnection( 'Requisites' )->prepare( $sql );

        $stmt->execute( [
            'inn' => $inn
        ] );

        return $stmt->fetchAll();
    }

    protected function diffDateRequisitesAndPki($certificatesDate, $requisitesDate) {
        usort( $requisitesDate, array( $this, 'date_sort' ) );

        $cerReqDate = [];
        foreach($certificatesDate as $cert) {
            $tempAr = ['DateStart'=>$cert->DateStart,'DateFinish'=>$cert->DateFinish];

            $i = 0;
            $reqOnlyDate = [];
            foreach($requisitesDate as $req) {
                if(strtotime($cert->DateStart) < strtotime($req['DateTime']) &&
                    strtotime($cert->DateFinish) > strtotime($req['DateTime'])) {
                    $reqOnlyDate[] = $req;
                    $i++;
                }
            }

            if(count($reqOnlyDate)>2) {
                $tempAr['Requisites'] = $reqOnlyDate[count($reqOnlyDate)-1];
            } else {
                foreach($requisitesDate as $req) {
                    if(strtotime($cert->DateStart) < strtotime($req['DateTime'])) {
                        $tempAr['Requisites'] = $req;
                        break;
                    }
                }

            }
            $cert->EdsUsage = $tempAr;

        }
        return $cerReqDate;
    }

    private static function date_sort($a, $b) {
        return strtotime($a['DateTime']) - strtotime($b['DateTime']);
    }

    private static function groupRequisitesDate($requisites) {
	    $reqArr = [];
	    foreach ($requisites as $req) {
	        if (array_key_exists($req['DateTime'] . '|' . $req['IDRequisites'],$reqArr)) {
	            if(array_key_exists($req['Series'].'|'.$req['Number'],$reqArr[$req['DateTime'] . '|' . $req['IDRequisites']])) {
                    $reqArr[$req['DateTime'] . '|' . $req['IDRequisites']]
                        [$req['Series'].'|'.$req['Number']]
                            [$req['RepresentativeRoleID']] = [
                                'RepresentativeRoleID' => $req['RepresentativeRoleID'],
                                'EdsName' => $req['EdsName'],
                                'EdsUsageModelID' => $req['EdsUsageModelID'],
                                'RoleName' => $req['RoleName']
                            ];
                } else {
                    $reqArr[$req['DateTime'] . '|' . $req['IDRequisites']][$req['Series'].'|'.$req['Number']] = [
                        'RepresentativeID' => $req['RepresentativeID'],
                        'Series' => $req['Series'],
                        'Number' => $req['Number'],
                        'EdsName' => $req['EdsName'],
                        $req['RepresentativeRoleID'] => [
                            'RepresentativeRoleID' => $req['RepresentativeRoleID'],
                            'EdsName' => $req['EdsName'],
                            'EdsUsageModelID' => $req['EdsUsageModelID'],
                            'RoleName' => $req['RoleName']
                        ]

                    ];
                }
            } else {
                $reqArr[$req['DateTime'] . '|' . $req['IDRequisites']] = [
                    'DateTime' => $req['DateTime'],
                    'IDRequisites' => $req['IDRequisites'],
                    $req['Series'].'|'.$req['Number'] => [
                        'RepresentativeID' => $req['RepresentativeID'],
                        'Series' => $req['Series'],
                        'Number' => $req['Number'],
                        'EdsName' => $req['EdsName'],
                        $req['RepresentativeRoleID'] => [
                            'RepresentativeRoleID' => $req['RepresentativeRoleID'],
                            'EdsName' => $req['EdsName'],
                            'EdsUsageModelID' => $req['EdsUsageModelID'],
                            'RoleName' => $req['RoleName']
                        ]
                    ]

                ];
            }
        }

	    return $reqArr;
    }

    protected function setUsageStatus( $uid, $status, $description ) {
		$client = new SoapClients\Api\RequisitesData();

		$success = $client->setUsageStatus(
			$client::SUBSCRIBER_TOKEN,
			$uid,
			(bool) $status,
			$description
		);

		if ( $success ) {
			$statuses = $client->getUsageStatuses( $client::SUBSCRIBER_TOKEN, $uid );

			return array_pop( $statuses );
		}

		return null;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-misc-form-colored.css';
		$this->context->css[] = 'resources/css/ui-requisites.css';

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
            $requisitesAll = $this->getRequisites( $inn, $uid );
			list( $requisites, $bindings ) = $requisitesAll;
		} catch ( \SoapFault $e ) {
//			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->faultstring;

			return;
		} catch ( \Exception $e ) {
//			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();

			return;
		}

		try {
			if ( $requisites && ! empty( $requisites->common ) ) {
				$this->variables->balance = $this->getSochiBillingBalance(
					$requisites->common->inn
				);
			} else {
				$this->variables->balance = null;
			}
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}

		if ( isset( $_POST['setUsageStatus'] ) ) {
			$status      = isset( $_POST['status'] ) ? (bool) $_POST['status'] : false;
			$description = isset( $_POST['description'] ) ? $_POST['description'] : null;

			try {
				$status = $this->setUsageStatus( $requisites->uid, $status, $description );

				if ( is_object( $status ) ) {
					$requisites->usageStatus = $status;
				} else {
					throw new \Exception( 'Не удалось назначить состояние обслуживания.' );
				}
			} catch ( \SoapFault $e ) {
				\Sentry\captureException( $e );
				$this->variables->errors[] = $e->faultstring;

				return;
			} catch ( \Exception $e ) {
				\Sentry\captureException( $e );
				$this->variables->errors[] = $e->getMessage();

				return;
			}
		}

		$this->variables->requisites = $requisites;
		$this->variables->bindings   = $bindings;

		try {
		    $certificates = $this->getPkiCertificates($requisites->common->inn);
            $requisitesDate = $this->getDateFromRequisites($inn);
            $requisitesDateFull = $this->groupRequisitesDate($requisitesDate);
            $cerReqDat = $this->diffDateRequisitesAndPki($certificates, $requisitesDateFull);

            $this->variables->certificates = $certificates;
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