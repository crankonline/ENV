<?php
namespace Unikum\Core\AutoLoaders;

final class Psr4 {
    const EXTENSION = 'php';

    protected static $map = [];

    public static function map($prefix, $path, $prepend = false){
        $prefix = trim($prefix, '\\') . '\\';

        if(!isset(self::$map[$prefix])){
            self::$map[$prefix] = [];
        }

        if($prepend){
            array_unshift(self::$map[$prefix], $path);
        } else {
            self::$map[$prefix][] = $path;
        }
    }

    public static function analyze($classpath){
        $prefix = $classpath;

        while(false !== ($pos = strrpos($prefix, '\\'))){
            $prefix = substr($classpath, 0, $pos + 1);
            $class  = substr($classpath, $pos + 1);

            if(self::load($prefix, $class)){
                return true;
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    public static function load($prefix, $class){
        if(!isset(self::$map[$prefix])){
            return false;
        }

        foreach(self::$map[$prefix] as $path){
            $file = $path . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.' . self::EXTENSION;

            if(is_file($file)){
                require_once $file;

                return true;
            }
        }

        return false;
    }

    public static function register(){
        spl_autoload_register(__CLASS__ . '::analyze');
    }
}
?>