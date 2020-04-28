<?php

namespace Environment\Modules;

class SochiZeroReport extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/SochiZeroReport/Default.html',
        'listen'   => 'action'
    ];

    public function send() {
        echo "Ответ от сервера - " . $this->get_zeroReport();
    }

    function get_zeroReport() {
        $url = "https://sochi.dostek.kg/trigger-zero-report";
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
        $this->variables->errors = [];

    }
}