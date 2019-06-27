<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients\Requisites as RequisitesClients;

use Environment\Soap\Types\Shared\Utils as Utils;

class Users extends \Unikum\Core\Module {
    const INN_REGEX = '/^((\d{10,10})|(\d{14,14}))$/';

    protected $config = [
        'render' => false
    ];

    protected function main(){
        if(isset($_GET['inn']) && preg_match(self::INN_REGEX, $_GET['inn'])){
            $inn = $_GET['inn'];

            try {
                $client = new RequisitesClients\Data();

                $data = $client->getByInn(API_SUBSCRIBER_TOKEN, $inn);

                if($data){
                    if(!(empty($data->common) || empty($data->common->representatives))){
                        foreach($data->common->representatives as $rep){
                            $rep->person->passport->issuingDate = Utils::dateReformat(
                                'Y-m-d',
                                'd.m.Y',
                                $rep->person->passport->issuingDate
                            );
                        }
                    }

                    if(!empty($data->usageStatus)){
                        $data->usageStatus->dateTime = Utils::dateReformat(
                            \DateTime::ISO8601,
                            'd.m.Y H:i:s',
                            $data->usageStatus->dateTime
                        );
                    }
                }

                $result = [
                    'success' => true,
                    'data'    => &$data
                ];
            } catch(\Exception $e) {
	            \Sentry\captureException($e);
                $result = [
                    'success'       => false,
                    'error-code'    => $e->getCode(),
                    'error-message' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'success' => true,
                'data'    => null
            ];
        }

        header('Content-Type: application/json');

        echo json_encode($result);
    }
}
?>
