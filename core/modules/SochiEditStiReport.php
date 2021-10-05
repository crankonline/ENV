<?php

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;
use Exception;

class SochiEditStiReport extends \Environment\Core\Module {
    const available_size = 200000;

    /**
     * @var string[]
     */
    protected $config = [
        'template' => 'layouts/SochiEditStiReport/Default.php',
        'listen'   => 'action'
    ];

    /**
     * @param string $uin
     * @param bool $withError
     * @return string
     * @throws Exception
     */
    protected function getData(string $uin, bool $withError = false):string {

        $client = new SoapClients\Api\StiReports();

        try {
            $resp = $client->getData(
                API_SUBSCRIBER_TOKEN,
                $uin);
        } catch (Exception $e) {
            $resp = "Не найдено оточета для данного uin <br/>".$e;
            if($withError) throw new Exception($resp);

        }

        return $resp;

    }

    /**
     *
     */
    public function download() {
        try {
            echo $this->getData($_GET['uin'], true);
            header("Content-Disposition: attachment; filename=" . $_GET['uin']);
        }
        catch (Exception $e) {
            http_response_code(404);
        }
        exit;
    }

    /**
     * @param string $uin
     * @param string $xml
     * @return string
     */
    protected function updateData(string $uin, string $xml) {

        $client = new SoapClients\Api\StiReports();

        try {
            return $client->updateData(
                API_SUBSCRIBER_TOKEN,
                $uin,
                $xml
            );
        } catch (Exception $e) {
            return "No reports found for this UIN";
        }

    }


    /**
     * @return void|null
     * @throws Exception
     */
    protected function main() {
        $this->context->css[] = 'resources/css/ui-requisites.css';
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-form-colored.css';
        $this->context->css[] = '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.17.1/build/styles/default.min.css';
        $this->context->js[] = '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.17.1/build/highlight.min.js';

        $uin = $_GET['uin'] ?? null;

        if ( ! $uin ) {
            return;
        }

        $this->variables->errors = [];
        $this->variables->success = [];

        if(!empty($_FILES['file'])) {
            $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        }

        $xml_save = $fileContent ?? $_POST['xml'] ?? null;
        if( $xml_save ) {
            $this->updateData($uin,$xml_save);
            $this->variables->success[] = "Отчет сохранен в кураторское приложение";
        }

        $report = $this->getData($uin);
        if (strpos($report, "Не найдено отчета для данного uin") !== false) {
            $this->variables->errors [] = $report;
            return;

        }

        $length ['rep_code_length'] = strlen($report);
        $length ['rep_xml_length'] = $length ['rep_code_length'];

        $this->variables->available_size = $this::available_size;
        $this->variables->length = $length;
        $this->variables->report = $report;
    }
}
