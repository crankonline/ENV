<?php
/**
 * Created by PhpStorm.
 * User: dex
 * Date: 23.09.19
 * Time: 11:43
 */

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Soap\Clients as SoapClients;

class ReportDecode extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/ReportDecode/Default.html',
		'listen'   => 'action'
	];

	private function decodeSfReport($uin) {
		$sql = <<<SQL
SELECT
	"r"."uin" as "uin",
	TO_CHAR("r"."input_date", 'DD.MM.YYYY HH24:MI:SS') as "input-date-time",
	"r"."period_month" as "period-month",
	"r"."period_year"  as "period-year",
	"r"."report_id"    as "report_id",
	"d"."id"		   as "d_id",
	"d"."xml_data"     as "xml"

FROM
	"sf_reporting"."pass_reports" as "r"
LEFT JOIN 
	"sf_reporting"."report_data" as "d" ON "d"."id" = "r"."report_id"    
WHERE
("r"."uin" = :uin)
ORDER BY
"r"."input_date" DESC;
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( [
			'uin' => $uin
		] );

	    return $stmt->fetch();
	}

	private function parseXmlSf($data) {
		$dom = new \DOMDocument;
		$dom->loadXML($data);
		$report = $dom->getElementsByTagName('EncryptionData');
		$serts = $dom->getElementsByTagName('CertNumber');
		$sertsArr = [];
		foreach ($serts as $sert) {
			array_push($sertsArr, $sert->nodeValue);
		}

		return ['rep' => $report[0]->nodeValue, 'certs' => $sertsArr];
	}

	protected function getPkiCertificates( $cert ) {
		return ( new SoapClients\PkiService() )->search( $cert );
	}

	private function decodeStiReport($uin, $form) {
		$sql = <<<SQL
SELECT *
FROM
   sti_reporting.$form as "f"

WHERE
  ("f"."uin" = :uins);
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		try {
			$stmt->execute( [
				'uins' => $uin
			] );
		} catch (\Exception $ex) {
		}
		return $stmt->fetch();
	}

	private function decodeNscReport($uin, $form) {
		$sql = <<<SQL
SELECT *
FROM 
	"stat_reporting"."$form" as "f"
WHERE
	("f"."uin" = :uins)
SQL;

		$stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

		$stmt->execute( [
			'uins' => $uin
		] );

		return $stmt->fetch();

	}

	private function formatXml($simpleXMLElement)
	{
		$xmlDocument = new \DOMDocument('1.0');
		$xmlDocument->preserveWhiteSpace = false;
		$xmlDocument->formatOutput = true;
		$xmlDocument->loadXML($simpleXMLElement);

		return $xmlDocument->saveXML();
	}

	protected function main() {
		$this->variables->errors = [];

		$type = isset( $_GET['type'] ) ? $_GET['type'] : null;
		$uin = isset( $_GET['uin'] ) ? $_GET['uin'] : null;

		if ( ! ( $type || $uin ) ) {
			return;
		}

		if ( $uin && ! preg_match( '/^\d{49,49}$/', $uin ) ) {
			$this->variables->errors[] = 'UIN должен состоять из 49 цифр';

			return;
		}

		try {
			if($type=='sf') {
				echo "sf ";
				$data = $this->decodeSfReport($uin);
				$report = $this->parseXmlSf($data['xml']);
				$repXml = base64_decode($report['rep']);
				$certs = $report['certs'];
				$certsArr = [];
				foreach ($certs as $cert) {
					array_push($certsArr, $this->getPkiCertificates($cert));
				}

				$this->variables->certs = $certsArr;
				$this->variables->report = $this->formatXml($repXml);
			}
			if($type=='sti') {
				echo "sti ";
				$form_sys_name = isset( $_GET['sys-name'] ) ? $_GET['sys-name'] : null;
				$data = $this->decodeStiReport($uin,lcfirst($form_sys_name));
				$report = $this->parseXmlSf($data['form_data']);
				$repXml = base64_decode($report['rep']);
				$certs = $report['certs'];
				//print_r($certs);
				$certsArr = [];
				foreach ($certs as $cert) {
					array_push($certsArr, $this->getPkiCertificates($cert));
				}

				$this->variables->certs = $certsArr;
				$this->variables->report = $this->formatXml($repXml);

//				print_r($data);
//				echo "f";
			}
			if($type=='nsc') {
				echo "nsc ";
				$form_sys_name = isset( $_GET['sys-name'] ) ? $_GET['sys-name'] : null;
				$data = $this->decodeNscReport($uin,lcfirst($form_sys_name));
//				print_r("1111",$data);
				$report = $this->parseXmlSf($data['form_data']);
				$repXml = base64_decode($report['rep']);
				$certs = $report['certs'];
				$certsArr = [];
				foreach ($certs as $cert) {
					array_push($certsArr, $this->getPkiCertificates($cert));
				}

				$this->variables->certs = $certsArr;
				$this->variables->report = $this->formatXml($repXml);
			}

			echo "ok";

		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}