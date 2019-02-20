<?php
namespace Unikum\Core\Dbms;

final class ConnectionManager {
    public static
        $configurations = [],
        $connections    = [];

    private function __construct(){}
    private function __clone(){}

    public static function configure($name, array $config){
        self::$configurations[$name] = $config;
    }

    public static function unconfigure($name = null){
        if($name === null){
            $names = array_keys(self::$configurations);

            foreach($names as $name){
                self::unconfigure($name);
            }
        } else {
            self::$configurations[$name] = null;

            unset(self::$configurations[$name]);
        }
    }

    public static function connect($name){
        if(!isset(self::$configurations[$name])){
            throw new \Exception("Connection \"{$name}\" is not configured.");
        }

        self::disconnect($name);

        $config = &self::$configurations[$name];

        try {
            $connection = new \PDO($config['dsn'], $config['user'], $config['password']);
            $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            throw new \Exception("Connection \"{$name}\" unestablished.");
        }

        self::$connections[$name] = $connection;
    }

    public static function disconnect($name = null){
        if($name === null){
            $names = array_keys(self::$connections);

            foreach($names as $name){
                self::disconnect($name);
            }
        } elseif (isset(self::$connections[$name])) {
            self::$connections[$name] = null;

            unset(self::$connections[$name]);
        }
    }

    public static function shutdown(){
        self::unconfigure();
        self::disconnect();
    }

    public static function getConnection($name){
        if(!isset(self::$connections[$name])){
            self::connect($name);
        }

        return self::$connections[$name];
    }
}
?>