<?php
namespace Environment\Soap\Clients\Sf;

class PayerInfoService extends \SoapClient {
    const WSDL = 'http://eleed.sf.kg:8040/PayerInfoService?wsdl';

    public function __construct(){
        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 60
        ];

        parent::__construct(self::WSDL, $options);
    }
}
?>