<?php
namespace Environment\Soap\Clients\Sf;

class PinInfoService extends \SoapClient {
    public function __construct(){
        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 10
        ];

        parent::__construct($_ENV['soapClients_sf_pinInfoService_wsdl'], $options);
    }
}
?>
