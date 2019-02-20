<?php
namespace Environment\Core;

abstract class DataLayer extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Environment';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }
}
?>