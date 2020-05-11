<?php

namespace Environment\Modules;

class SochiZeroReport extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/SochiZeroReport/Default.php',
        'listen'   => 'action'
    ];

    public function send() {
        if((isset($_POST["module-id"]) && isset($_POST["day"])) &&
            ($_POST['module-id'] != "(выбрать)" && $_POST["day"] != "(выбрать)")){

            $this->variables->response = "Ответ от сервера - " . $this->get_zeroReport();

        } else {
            $this->variables->errors[] = 'Необходимо выбрать тип отправляемых нулевых отчетов и число!';
        }
    }

    function get_zeroReport() {

            $url = $_ENV['core_modules_sochiZeroReport']."?".$_POST['module-id']."=".$_POST['day'];
            $options = array(
                CURLOPT_RETURNTRANSFER => true,   // return web page
                CURLOPT_HEADER         => false,  // don't return headers
                CURLOPT_FOLLOWLOCATION => true,   // follow redirects
                CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
                CURLOPT_ENCODING       => "",     // handle compressed
                CURLOPT_USERAGENT      => "test", // name of client
                CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
                CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
                CURLOPT_TIMEOUT        => 120,    // time-out on response
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);

            $content  = curl_exec($ch);

            curl_close($ch);

            return $content;


    }

    protected function main() {

        $this->context->css[] = 'resources/css/ui-misc-form.css';

    }
}