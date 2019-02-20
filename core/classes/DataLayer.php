<?php
namespace Unikum\Core;

use Unikum\Core\Dbms\ConnectionManager as Connections;

abstract class DataLayer {
    protected $dbms;

    public function __construct($connection){
        if(is_object($connection) && ($connection instanceof \PDO)){
            $this->dbms = $connection;
        } elseif(is_string($connection)){
            $this->dbms = Connections::getConnection($connection);
        } else {
            throw new \InvalidArgumentException('Connection must be an instance of PDO or string.');
        }
    }

    public function __destruct(){
        unset($this->dbms);
    }

    protected function toParams($row, $map){
        $result = [];

        foreach($map as $src => $dst){
            $result[$dst === null ? $src : $dst] = isset($row[$src]) ? $row[$src] : null;
        }

        return $result;
    }
}
?>