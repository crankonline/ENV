<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class Nwa extends \Environment\Core\Module {
    const
        URL_MJ  = 'http://register.minjust.gov.kg/register/',
        URL_STI = 'https://salyk.kg/TaxPayer/Info?Type=ByTin&Tin=';

    protected
        $config = [
            'template' => 'layouts/Nwa/Default.html'
        ],
        $types = [
            0 => 'ИНН',
            1 => 'ОКПО',
            2 => 'Рег. номер СФ'
        ];

	function getSslPage($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'WEWE');

//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			print curl_error($ch);
		}
		curl_close($ch);
		return $result;
	}

    protected function getSf($type, $value){

        $payerInfo = new \stdClass();

        switch($type){
            default:
            case 0:
                $payerInfo->searchField = 'INN';
            break;

            case 1:
                $payerInfo->searchField = 'OKPO';
            break;

            case 2:
                $payerInfo->searchField = 'PayerId';
            break;
        }

        $payerInfo->values = [ $value ];

        try {
            $client = new SoapClients\Sf\PayerInfoService();

            $result = $client->GetPayersInfo($payerInfo)->GetPayersInfoResult;

            $result = is_object($result->PayerInfo)
                ? [ $result->PayerInfo ]
                : $result->PayerInfo;

            if(empty($result) || (!$result[0]->PayerName)){
                $result = [];
            }
        } catch(\SoapFault $f) {
	        \Sentry\captureException($e);
            throw new \Exception($f->faultstring);
        }

        return $result;
    }

    protected function getSti($type, $value){

    	$district = null;
	    $companyname = null;
	    $inn = $value;
	    $url = self::URL_STI . $inn;
	    $code = $this->getSslPage($url);
	    $doc = new \DOMDocument();
	    if (!@$doc->loadHTML($code))
	    { throw new \Exception('TaxPayer --> Невозможно обработать ответ.'); }

	    $div_cont = $doc->getElementById('ByTin');
	    $rows_table = $div_cont->getElementsByTagName('tr');
	    if ($rows_table->length != 2)
	    { throw new \Exception('TaxPayer --> Сервис вернул не действительное значение.'); }

	    foreach ($rows_table as $key => $row){
		    if (!$key)
		    { continue; }

		    $cells = $row->getElementsByTagName('td');
		    $district =$cells[1]->nodeValue;
		    $companyname = $cells[2]->nodeValue;
	    }
	    echo $companyname.' '.$district;

        return [$inn, $district, $companyname];

    }

    protected function getMj($type, $value){
        $query = [];

        switch($type){
            default:
            case 0:
                $query['tin'] = $value;
            break;

            case 1:
                $query['okpo'] = $value;
            break;

            case 2:
                throw new \Exception('Тип реквизита для поиска не поддерживается.');
            break;
        }

        $scrapUrl = self::URL_MJ . 'SearchAction.seam' . '?' . http_build_query($query);
        $linkUrl  = self::URL_MJ . 'Public.seam';

        $result = @file_get_contents($scrapUrl);

        if(!$result){
            throw new \Exception('Ничего не найдено, либо сервис не доступен.');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        if(!@$dom->loadHTML($result)){
            throw new \Exception('Невозможно обработать ответ.');
        }

        $result = [];

        $table = $dom->getElementById('searchActionForm:searchAction');

        if(!$table){
            return $result;
        }

        $rows = $table->getElementsByTagName('tr');

        if($rows->length < 2){
            return $result;
        }

        foreach($rows as $rIndex => $row){
            if(!$rIndex){
                continue;
            }

            $cells = $row->getElementsByTagName('td');

            $rRow = [];

            foreach($cells as $cIndex => $cell){
                switch ($cIndex) {
                    case 0:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                        $rRow[$cIndex] = trim($cell->nodeValue);
                    break;

                    case 1:
                        $rRow[$cIndex] = trim($cell->childNodes->item(0)->nodeValue);
                    break;

                    case 7:
                        $href  = $cell->childNodes->item(0)->getAttribute('href');
                        $query = parse_url($href, PHP_URL_QUERY);

                        $rRow[$cIndex] = $linkUrl .'?' . $query;
                    break;
                }
            }

            $result[] = $rRow;
        }

        return $result;
    }

    protected function getNsc($type, $value){
        return [];
        try {
            $params = [];
            $values = [];

            switch($type){
                default:
                case 0:
                    $params[] = '(k_soc = CAST(? AS VARCHAR))';
                    $values[] = $value;
                break;

                case 1:
                    $params[] = '(k_pred = CAST(? AS VARCHAR))';
                    $values[] = $value;
                break;

                case 2:
                    throw new \Exception('Тип реквизита для поиска не поддерживается.');
                break;
            }

            $params = $params ? 'WHERE ' . implode('AND', $params) : '';

            $sql = <<<SQL
SELECT
    k_pred as okpo,
    name_s as fullname,
    k_soc as inn,
    k_npu as soate,
    adres_obj as juristicAddress,
    adresf as physicalAddress,
    oked_4 as gked2,
    oked_3 as gked3,
    fio as chief,
    t_on as phone,
    n_ter as region,
    n_okd as gked2Name,
    name as gked3Name,
    n_opf as legalForm,
    n_sob as ownershipForm,
    n_sek as ecoSector
FROM
    KATME
        INNER JOIN SPRTER
            ON k_ter = k_npu
        INNER JOIN SPROKD
            ON k_okd = oked_4
        INNER JOIN OKED3
            ON kod = oked_3
        INNER JOIN SPROPF
            ON k_opf = SUBSTRING(CAST(ktp AS VARCHAR), 1, 2)
        INNER JOIN SPRSOB
            ON k_sob = fsob
        INNER JOIN SPRSEK
            ON k_sek = sek_ek
{$params};
SQL;

            $stmt = Connections::getConnection('Egrse')->prepare($sql);

            $stmt->execute($values);

            return $stmt->fetchAll();
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            throw new \Exception($e->getMessage());
        }
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-nwa.css';

        $this->variables->errors = [
            'sf'  => [],
            'mj'  => [],
            'nsc' => [],
            'sti' => []
        ];

        $type  = isset($_GET['type']) ? (int)$_GET['type'] : 0;
        $value = isset($_GET['value']) ? $_GET['value'] : null;

        if(!isset($this->types[$type])){
            $type = 0;
        }

        $this->variables->types  = &$this->types;
        $this->variables->cType  = $type;
        $this->variables->cValue = $value;

        if(empty($value)){
            return;
        }

        try {
            $this->variables->sfData = $this->getSf($type, $value);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors['sf'][] = $e->getMessage();
        }

        try {
            $this->variables->mjData = $this->getMj($type, $value);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors['mj'][] = $e->getMessage();
        }

        try {
            $this->variables->nscData = $this->getNsc($type, $value);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors['nsc'][] = $e->getMessage();
        }

        try {
            $this->variables->stiData = $this->getSti($type, $value);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors['sti'][] = $e->getMessage();
        }
    }
}
?>