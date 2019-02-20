<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class UidParser extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/UidParser/Default.html',
        'listen'   => 'action'
    ];

    protected static function parse($uid){
        if(!preg_match('/^\d{23}$/', $uid)){
            throw new \Exception('Идентификатор пользователя должен состоять из 23 цифр');
        }

        return [
            'UID'        => $uid,
            'Year'       => substr($uid, 0, 4),
            'Month'      => substr($uid, 4, 2),
            'Day'        => substr($uid, 6, 2),
            'Hour'       => substr($uid, 8, 2),
            'Minute'     => substr($uid, 10, 2),
            'Second'     => substr($uid, 12, 2),
            'Subscriber' => substr($uid, 14, 3),
            'Number'     => substr($uid, 17)
        ];
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-uid-parser.css';

        $this->variables->errors = [];

        $uid = isset($_GET['uid']) ? $_GET['uid'] : null;

        $this->variables->cUid = $uid;

        if(empty($uid)){
            return;
        }

        try {
            $this->variables->data = $this->parse($uid);
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>