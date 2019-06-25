<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients as SoapClients;

class Activities extends \Unikum\Core\Module {
    protected $config = [
        'render' => false,
        'listen' => 'action'
    ];

    public function getByActivity(){
        $activityId = empty($_GET['activity']) ? null : $_GET['activity'];

        try {
            $client = new SoapClients\Requisites\Meta();

            $result = [
                'success'    => true,
                'result-set' => $client->getCommonActivities(API_SUBSCRIBER_TOKEN, $activityId)
            ];
        } catch (\Exception $e) {
            $result = [
                'success'       => false,
                'error-code'    => $e->getCode(),
                'error-message' => $e->getMessage()
            ];
        }

        $this->result = $result;
    }

    public function getByGked(){
        $gked = empty($_GET['gked']) ? null : $_GET['gked'];

        if(preg_match('/^(\d+\.{1,1})*\d+$/', $gked)){
            try {
                $client = new SoapClients\Requisites\Meta();

                $result = [
                    'success'  => true,
                    'activity' => $client->getCommonActivityByGked(API_SUBSCRIBER_TOKEN, $gked)
                ];
            } catch (\Exception $e) {
                $result = [
                    'success'       => false,
                    'error-code'    => $e->getCode(),
                    'error-message' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'success'  => true,
                'activity' => null
            ];
        }

        $this->result = $result;
    }

    protected function main(){
        if(isset($this->result)){
            header('Content-Type: application/json');

            echo json_encode($this->result);
        }
    }
}
?>