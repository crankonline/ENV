<?php
namespace Environment\Soap\Clients\Tunduk;

class RequisitesData extends \SoapClient {

    const SUBSCRIBER_ID = '1';
    const SUBSCRIBER_NAME = 'XXX';
    const SUBSCRIBER_RESPONSIBLE = 'YYY';
    const SUBSCRIBER_PHONES = '974 99 92';

    public function __construct(){


        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 60,

        ];

        parent::__construct($_ENV['soapClients_Tunduk_RequisitesData_wsdl'], $options);
    }
}
?>