<?php
namespace Environment\Soap\Clients\Tunduk;

class RequisitesData extends \SoapClient {

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



        parent::__construct($_ENV['soapClients_Tunduk_RequisitesData_wsdl'], $options);
    }
}
?>