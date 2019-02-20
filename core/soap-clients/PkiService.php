<?php
namespace Environment\Soap\Clients;

class PkiService extends \SoapClient {
    // const WSDL = 'http://pkiservice-test.dostek.kg/pkiservice.php?wsdl';
    const WSDL = 'http://pkiservice.dostek.kg/pkiservice.php?wsdl';

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