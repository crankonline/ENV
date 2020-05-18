<?php

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;

class SochiEditStiReport extends \Environment\Core\Module {
    const available_size = 200000;

    protected $config = [
        'template' => 'layouts/SochiEditStiReport/Default.php',
        'listen'   => 'action'
    ];

    function getData(string $uin) {

        $client = new SoapClients\Api\StiReports();

        $resp = '';
        try {
            $resp = $client->getData(
                API_SUBSCRIBER_TOKEN,
                $uin);
        } catch (\Exception $e) {
            $resp = "Не найдено оточета для данного uin <br/>".$e;

        }

        return $resp;

    }

    function updateData(string $uin, string $xml) {

        $client = new SoapClients\Api\StiReports();


        try {
            return $client->updateData(
                API_SUBSCRIBER_TOKEN,
                $uin,
                $xml
            );
        } catch (\Exception $e) {
            return "No reports found for this UIN";
        }

    }


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

        $xml_save = $_POST['xml'] ?? null;
        if( $xml_save ) {
            $this->updateData($uin,$xml_save);
            $this->variables->success[] = "Отчет сохранен в кураторское приложение";
        }

        $report = $this->getData($uin);
        if (strpos($report, "Не найдено оточета для данного uin") !== false) {
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