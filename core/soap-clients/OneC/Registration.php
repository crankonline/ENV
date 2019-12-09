<?php
namespace Environment\Soap\Clients\OneC;

final class Registration extends \SoapClient {
    const WSDL  = 'http://1c.dostek.kg:8080/dtb/ws/SOCHI/?wsdl';

    public function __construct(){
        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 60,
            'login'    => HTTP_AUTH_1C_USR,
            'password' => HTTP_AUTH_1C_PWD
        ];

        parent::__construct(self::WSDL, $options);
    }
}
?>