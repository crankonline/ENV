<?php
namespace Environment\Soap\Clients\Api;

class StiReports extends \SoapClient {
//    const
//        WSDL = 'http://api.dostek.test/StiReports.php?wsdl';

//    const
//        SUBSCRIBER_TOKEN = '72bba1692ed5afdc303d415caa19c4259670ca9a23910f4797d783c2bfbe41e9';

    public function __construct(){
        $login    = 'api-' . date('z') . '-user';
        $password = 'p@-' . round(date('z') * 3.14 * 15 * 2.7245 / 4 + 448) . '$';

        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 60,

            'login'    => $login,
            'password' => $password
        ];

        parent::__construct($_ENV['soapClients_api_stiReports_wsdl'], $options);
    }
}
?>