<?php
namespace Environment\Soap\Clients\Sf;

class PayerInfoService extends \SoapClient {
    public function __construct(Array $addOptions = []){
        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 10
        ];

        foreach($addOptions as $index => $addOption){
            $options[$index] = $addOption;
        }
        ini_set('default_socket_timeout', $options['connection_timeout']);

        parent::__construct($_ENV['soapClients_sf_payerInfoService_wsdl'], $options);
    }
}
?>
