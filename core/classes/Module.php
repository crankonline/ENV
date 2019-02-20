<?php
namespace Unikum\Core;

abstract class Module {
    private
        $defaults = [
            'template' => null,
            'plugins'  => null,
            'context'  => null,
            'listen'   => false,
            'render'   => true
        ];

    protected
        $config = [],
        $context,
        $variables;

    public function __construct(array $config = []){
        $this->config = (object)array_merge($this->defaults, $config, $this->config);

        $this->context   = isset($this->config->context) ? $this->config->context : new \stdClass;
        $this->variables = new \stdClass;

        $main       = get_called_class() . '::main';
        $reflection = new \ReflectionMethod($main);

        if($reflection->isPublic()){
            throw new \LogicException("Method \"{$main}()\" can not have public visibility scope.");
        }

        unset($main, $reflection);
    }

    public function __destruct(){
        unset($this->config, $this->variables, $this->defaults);
    }

    protected function main(){
        return null;
    }

    protected function suppress(){
        $this->config->template = null;
        $this->config->render   = false;
    }

    protected function execute(){
        if($this->config->listen && isset($_REQUEST[$this->config->listen])){
            $class  = get_called_class();
            $method = $_REQUEST[$this->config->listen];

            if(!method_exists($class, $method)){
                throw new \LogicException("Invalid action \"{$class}::{$method}()\".");
            }

            $reflection = new \ReflectionMethod($class, $method);

            if($reflection->isConstructor() || $reflection->isDestructor()) {
                throw new \LogicException('Access to the constructor or destructor is restricted.');
            } elseif(!$reflection->isPublic()) {
                throw new \LogicException("Access to the action \"{$class}::{$method}()\" is restricted.");
            } elseif($reflection->isStatic()) {
                throw new \LogicException("Access to the static method \"{$class}::{$method}()\" is restricted.");
            }

            unset($reflection);

            $this->{$method}();
        }

        $this->main();
        $this->render();
    }

    final protected function render(){
        if(!$this->config->render){
            return false;
        }

        if(!isset($this->config->template)){
            throw new \LogicException('Template file was not specified.');
        }

        $this->template = $this->config->template;

        if(!is_file($this->template)){
            throw new \LogicException('Template file "' . $this->template . '" was not found.');
        }

        $this->buffer = (array)$this->variables;

        extract($this->buffer);

        unset($this->buffer);

        if(empty($this->config->plugins)){
            require $this->template;
        } else {
            ob_start();

            require $this->template;

            $content = ob_get_clean();

            $aliases = array_keys($this->config->plugins);
            $plugins = array_values($this->config->plugins);
            $values  = [];

            foreach($aliases as &$alias){
                $alias = "{plugin:{$alias}}";
            }

            $config = [
                'context' => $this->context
            ];

            foreach($plugins as &$module){
                ob_start();
                $module::run($config);
                $values[] = ob_get_clean();
            }

            echo str_replace($aliases, $values, $content);
        }

        unset($this->template);

        return true;
    }

    public function redirect($url, $immediately = true){
        if(is_string($url)){

            $url   = ltrim($url, '/');
            $parts = parse_url($url);

            if(!isset($parts['scheme'], $parts['path'])){
                $url = SYSTEM_HOST . $url;
            }

        } elseif(is_array($url)) {
            $url = SYSTEM_HOST . basename($_SERVER['SCRIPT_NAME']) . '?' . http_build_query($url);
        }

        header('Location: ' . $url, true, 302);

        if($immediately){
            exit;
        }
    }

    final public static function run(){
        $reflection = new \ReflectionClass(get_called_class());
        $instance   = $reflection->newInstanceArgs(func_get_args());

        unset($reflection);

        $instance->execute();

        unset($instance);
    }
}
?>