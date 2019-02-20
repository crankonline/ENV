<?php
namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;

class PkiSearch extends \Environment\Core\Module {
    protected $config = [
        'template'   => 'layouts/PkiSearch/Default.html',
        'listen'     => 'action'
    ];

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-pki-search.css';

        $this->variables->errors = [];

        $value = isset($_GET['value']) ? $_GET['value'] : null;

        if(!$value){
            return;
        }

        $this->variables->cValue = $value;

        try {
            $clientDTG = new SoapClients\PkiService();
            $clientUBR = new SoapClients\PkiServiceUBR();

            $certificatesDTG = $clientDTG->search($value);
            $certificatesUBR = $clientUBR->search($value);

            if(!is_null($certificatesDTG)) {
                foreach ($certificatesDTG as $record) {
                    $record->CA = 'DTG';
                }
            }

            if(!is_null($certificatesUBR)) {
                foreach ($certificatesUBR as $record) {
                    $record->CA = 'UBR';
                }
            }

            $this->variables->certificates = array_merge(
                is_null($certificatesDTG) ? [] : $certificatesDTG,
                is_null($certificatesUBR) ? [] : $certificatesUBR
            );

           // var_dump($this->variables->certificates);

          usort($this->variables->certificates, function($a, $b) {//сортировка
                    return ($b->DateFinish > $a->DateFinish);
                });


        } catch(\SoapFault $e) {
            $this->variables->errors[] = $e->faultstring;
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>