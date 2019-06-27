<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class DataHarvester extends \Environment\Core\Module {
    const
        URL_MJ  = 'http://register.minjust.gov.kg/register/',
        URL_STI = 'http://ws.sti.gov.kg/tax/';

    protected
        $config = [
            'template' => 'layouts/DataHarvester/Default.html',
            'listen'   => 'action'
        ];

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
	        \Sentry\captureException($f);
            throw new \Exception($f->faultstring);
        }

        return $result;
    }

    protected function getSti($type, $value){
        switch($type){
            default:
            case 0:
                // passed
            break;

            case 1:
            case 2:
                throw new \Exception('Тип реквизита для поиска не поддерживается.');
            break;
        }

        $query = http_build_query([
            'compn' => 1,
            'compa' => 1,
            'compd' => 1,
            'compv' => 1,
            'tin'   => $value
        ]);

        $url = self::URL_STI . 'tin_list_all_out.idc' . '?' . $query;

        $content = @file_get_contents($url);
        $result  = [];

        if(!$content){
            return $result;
        }

        preg_match_all('/\'(.+)\'/', $content, $matches);

        $matches = $matches[1];

        foreach($matches as $key => $match){
            $match = trim(str_replace('&nbsp', ' ', strip_tags($match)));

            if($match && $key){
                $match = preg_replace('/\s+/', ' ', $match);
                $match = iconv('Windows-1251', 'UTF-8', $match);

                $result[] = $match;
            }
        }

        return $result;
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

    public function scrap(){
        $inn = empty($_POST['inn']) ? null : $_POST['inn'];

        $result = [];

        try {
            $result['sf'] = $this->getSf(0, $inn);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $result['sf'] = [ 'message' => $e->getMessage() ];
        }

        try {
            $result['sti'] = $this->getSti(0, $inn);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $result['sti'] = [ 'message' => $e->getMessage() ];
        }

        try {
            $result['mj'] = $this->getMj(0, $inn);
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $result['mj'] = [ 'message' => $e->getMessage() ];
        }

        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-data-harvester.css';

        $this->context->js[] = 'resources/js/jquery-2.1.4.min.js';
    }
}
?>