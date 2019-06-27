<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\DataLayers\OnlineStatements\Statements as StatementsSchema;

class Statements extends \Unikum\Core\Module {
    const
        INN_REGEX = '/^((\d{10,10})|(\d{14,14}))$/';

    const
        MODE_REVISION = 1,
        MODE_PAID     = 2,
        MODE_REJECTED = 3;

    protected $config = [
        'render' => false
    ];

    protected function main(){
        if(isset($_GET['inn']) && preg_match(self::INN_REGEX, $_GET['inn'])){
            $inn  = $_GET['inn'];
            $mode = isset($_GET['mode']) ? abs((int)$_GET['mode']) : null;

            switch($mode){
                default:
                case self::MODE_REVISION:
                    $status = StatementsSchema\Statements::STATUS_REVISION;
                break;

                case self::MODE_PAID:
                    $status = StatementsSchema\Statements::STATUS_PAID;
                break;

                case self::MODE_REJECTED:
                    $status = StatementsSchema\Statements::STATUS_REJECTED;
                break;
            }

            try {
                $dlStatements = new StatementsSchema\Statements();

                $statement = $dlStatements->getByInnAndStatus($inn, $status);

                if($statement){
                    $statement['data'] = json_decode($statement['data']);

                    $dlFiles = new StatementsSchema\Files();

                    $statement['files'] = $dlFiles->getByStatement($statement['id']);
                }

                $result = [
                    'success' => true,
                    'data'    => &$statement
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

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
?>