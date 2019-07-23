<?php
namespace Environment\DataLayers\Requisites\Meta;
class LegalForm  extends \Unikum\Core\DataLayer {
	const DEFAULT_CONNECTION = 'Requisites';

	public function __construct($dbms = null){
		parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
	}

	public function getLegalForm($filters){

		$params = [];
		$values = [];

//		if(array_key_exists('payment-id', $filters)){
//			$params[] = '("d-pmtitm"."PaymentID" = :paymentId)';
//
//			$values['paymentId'] = $filters['payment-id'];
//		}

		$params = $params ? 'WHERE ' . implode(' AND ', $params) : null;
		$params = "";

		$sql = <<<SQL
SELECT
    *
FROM
    "Common"."LegalForm" as "c-lf"
        
{$params}
ORDER BY
    "c-lf"."Name";
SQL;

		$stmt = $this->dbms->prepare($sql);

		$stmt->execute($values);

		return $stmt->fetchAll();
	}
}