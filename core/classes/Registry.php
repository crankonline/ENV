<?php
namespace Unikum\Core;

final class Registry {
    protected static $container = array();

    private function __construct(){}
    private function __clone(){}

    public static function get($key){
        return self::contains($key) ? self::$container[$key] : null;
    }

    public static function set($key, $value){
        self::$container[$key] = $value;
    }

    public static function remove($key){
        unset(self::$container[$key]);
    }

    public static function contains($key){
        return array_key_exists($key, self::$container);
    }

    public static function purge(){
        self::$container = [];
    }
}
?>