<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\Soap\Clients as SoapClients;

class Tokens extends \Unikum\Core\Module {
    protected $config = [
        'render' => false,
        'listen' => 'action'
    ];

    public function check(){
        $deviceSerial = empty($_POST['device-serial']) ? null : $_POST['device-serial'];

        try {
            $client = new SoapClients\RegToken();

            $result = [
                'success' => true,
                'result'  => $client->checktoken($deviceSerial)
            ];
        } catch (\Exception $e) {
	        \Sentry\captureException($e);
            $result = [
                'success'       => false,
                'error-code'    => $e->getCode(),
                'error-message' => $e->getMessage()
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