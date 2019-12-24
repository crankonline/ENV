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
    const available_size = 200000;
	protected $config = [
		'template' => 'layouts/ReportDecode/Default.php',
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

	private function parseXml($data) {
	    if (is_null($data)) { return null; }
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
        $this->context->css[] = 'resources/css/ui-requisites.css';
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-form-colored.css';
        $this->context->css[] = '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.17.1/build/styles/default.min.css';
        $this->context->js[] = '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.17.1/build/highlight.min.js';


        $type = isset( $_GET['type'] ) ? $_GET['type'] : null;
		$uin = isset( $_GET['uin'] ) ? $_GET['uin'] : null;
		$download = isset( $_GET['download'] ) ? $_GET['download'] : null;

		if ( ! ( $type || $uin ) ) {
			return;
		}

		if ( $uin && ! preg_match( '/^\d{49,49}$/', $uin ) ) {
			$this->variables->errors[] = 'UIN должен состоять из 49 цифр';

			return;
		}

		try {
		    $report = null;
		    $length = array();
            if ($type == 'sf') {
                echo "sf ";
                $data = $this->decodeSfReport($uin);
                $length ['dtg_cont_length'] = strlen($data['form_data']);
                $report = $this->parseXml($data['xml']);

            }
            if ($type == 'sti') {
                echo "sti ";
                $form_sys_name = isset($_GET['sys-name']) ? $_GET['sys-name'] : null;
                $data = $this->decodeStiReport($uin, lcfirst($form_sys_name));
                $length ['dtg_cont_length']= strlen($data['form_data']);
                $report = $this->parseXml($data['form_data']);

            }
            if ($type == 'nsc') {
                echo "nsc ";
                $form_sys_name = isset($_GET['sys-name']) ? $_GET['sys-name'] : null;
                $data = $this->decodeNscReport($uin, lcfirst($form_sys_name));
                $length ['dtg_cont_length']= strlen($data['form_data']);
                $report = $this->parseXml($data['form_data']);
            }
            if ($report != null) {
                $repXml = base64_decode($report['rep']);
                if($download) {
                    header('Content-Type: text/plain');
                    header("Content-disposition: attachment; filename=\"" . $uin . ".xml\"");
                    echo $repXml;
                    exit;

                }
                $length ['rep_code_length'] = strlen($report['rep']);
                $length ['rep_xml_length'] = strlen($repXml);
                $certs = $report['certs'];
                $certsArr = [];
                foreach ($certs as $cert) {
                    array_push($certsArr, $this->getPkiCertificates($cert));
                }

                $this->variables->available_size = $this::available_size;
                $this->variables->length = $length;
                $this->variables->certs = $certsArr;
                $this->variables->report = $this->formatXml($repXml);
            } else {
                $this->variables->errors[] = 'Контейнер по запрашиваемому uin отсутствует';
            }

//			echo "ok";

		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}