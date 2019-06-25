<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients as SoapClients;

use Environment\Soap\Types\Shared\Utils as Utils;

class UsageStatuses extends \Unikum\Core\Module {
    protected $config = [
        'render' => false,
        'listen' => 'action'
    ];

    public function getStatuses(){
        $uid = empty($_POST['uid']) ? null : $_POST['uid'];

        if(preg_match('/^\d{23,23}$/', $uid)){
            try {
                $client = new SoapClients\Requisites\Data();

                $statuses = $client->getUsageStatuses(API_SUBSCRIBER_TOKEN, $uid);

                foreach($statuses as $status) {
                    $status->dateTime = Utils::dateReformat(
                        \DateTime::ISO8601,
                        'd.m.Y H:i:s',
                        $status->dateTime
                    );
                }

                $result = [
                    'success'    => true,
                    'result-set' => &$statuses
                ];
            } catch(\Exception $e) {
                $result = [
                    'success'       => false,
                    'error-code'    => $e->getCode(),
                    'error-message' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'success'    => true,
                'result-set' => []
            ];
        }

        $this->result = $result;
    }

    public function setStatus(){
        $uid         = empty($_POST['uid']) ? null : $_POST['uid'];
        $isActive    = empty($_POST['is-active']) ? false : (bool)$_POST['is-active'];
        $description = empty($_POST['description']) ? null : $_POST['description'];

        if(preg_match('/^\d{23,23}$/', $uid)){
            try {
                $client = new SoapClients\Requisites\Data();

                $result = [
                    'success' => true,
                    'result'  => $client->setUsageStatus(
                        API_SUBSCRIBER_TOKEN,
                        $uid,
                        $isActive,
                        $description
                    )
                ];
            } catch(\Exception $e) {
                $result = [
                    'success'       => false,
                    'error-code'    => $e->getCode(),
                    'error-message' => $e->getMessage()
                ];
            }
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