<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients as SoapClients;

class Settlements extends \Unikum\Core\Module {
    protected $config = [
        'render' => false
    ];

    protected function main(){
        $districtId = $regionId = null;

        if(!empty($_GET['district'])){
            $districtId = $_GET['district'];
        } elseif(!empty($_GET['region'])) {
            $regionId = $_GET['region'];
        }

        try {
            $client = new SoapClients\Requisites\Meta();

            $result = [
                'success'    => true,
                'result-set' => $client->getCommonSettlements(
                    API_SUBSCRIBER_TOKEN,
                    $regionId,
                    $districtId
                )
            ];
        } catch (\Exception $e) {
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