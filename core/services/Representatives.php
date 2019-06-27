<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients as SoapClients;

class Representatives extends \Unikum\Core\Module {
    protected $config = [
        'render' => false
    ];

    protected function formatISO8601date($date){
        $date = \DateTime::createFromFormat('Y-m-d', $date);

        return $date ? $date->format('d.m.Y') : null;
    }

    protected function main(){
        $result = [
            'success' => true,
            'result'  => []
        ];

        if(!isset($_GET['series'], $_GET['number'])){
            echo json_encode($result);
            return;
        }

        try {
            $client = new SoapClients\Requisites\Data();

            $person = $client->getPersonByPassport(
                API_SUBSCRIBER_TOKEN,
                $_GET['series'],
                $_GET['number']
            );

            if($person){
                $person->passport->issuingDate = $this->formatISO8601date(
                    $person->passport->issuingDate
                );
            }

            $result = [
                'success' => true,
                'result'  => $person
            ];
        } catch (\Exception $e) {
	        \Sentry\captureException($e);
            $result = [
                'success'       => false,
                'error-code'    => $e->getCode(),
                'error-message' => $e->getMessage()
            ];
        }

        header('Content-Type: application/json');

        echo json_encode($result);
    }
}
?>