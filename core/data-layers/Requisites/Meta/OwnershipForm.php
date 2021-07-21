<?php
namespace Environment\DataLayers\Requisites\Meta;
class OwnershipForm  extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Requisites';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    /**
     * @return array
     */
    public function getOwnershipForms(): array {

        $sql = <<<SQL
            SELECT * FROM "Common"."OwnershipForm"
SQL;

        $stmt = $this->dbms->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}