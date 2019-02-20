<?php
namespace Unikum\Core;

class Router {
    static $routes = [];

    public static function map(array $route){
        if(func_num_args() > 1){
            foreach(func_get_args() as $route){
                self::map($route);
            }
        } else {
            self::$routes[] = $route;
        }
    }

    public static function navigate(){

        foreach(self::$routes as &$route){
            $module    = isset($route['module']) ? $route['module'] : false;

            $condition = isset($route['condition']) && is_callable($route['condition'])
                ? $route['condition']
                : false;



            if(!$condition) {
                return $module;
            } elseif($condition()) {
                return $module;
            }
        }

        return false;
    }
}
?>