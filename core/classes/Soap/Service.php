<?php
namespace Unikum\Core\Soap;

class Service {
    private
        $defaults = [
            'wsdl'           => null,
            'enableHttpAuth' => false,
            'serverSettings' => []
        ];

    protected
        $config = [];

    public function __construct(array $config = []){
        $this->config = (object)array_merge($this->defaults, $config, $this->config);
    }

    public function __destruct(){
        unset($this->defaults, $this->config);
    }

    protected function httpAuthentificate($user, $password){
        return true;
    }

    final public static function listen(){
        $reflection = new \ReflectionClass(get_called_class());
        $instance   = $reflection->newInstanceArgs(func_get_args());

        $wsdl       = &$instance->config->wsdl;
        $isHttpAuth = &$instance->config->enableHttpAuth;
        $settings   = &$instance->config->serverSettings;

        if(strtolower($_SERVER['QUERY_STRING']) == 'wsdl'){
            header('Content-Type: text/xml; charset=utf-8');

            echo file_get_contents($wsdl);
        } else {
            if($isHttpAuth){
                $user     = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
                $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;

                if(($user === null) && ($password === null)){
                    header('WWW-Authenticate: Basic realm="My Realm"');

                    return http_response_code(401);
                } elseif(!$instance->httpAuthentificate($user, $password)) {
                    return http_response_code(403);
                }
            }

            $server = new \SoapServer($wsdl, $settings);
            $server->setObject($instance);
            $server->handle();
        }

        return http_response_code();
    }
}
?>