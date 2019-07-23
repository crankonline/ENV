<?php
/**
 * Reregister
 */
namespace Environment\DataLayers\Reregister\Statistics;

class Actions extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Reregister';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function register(array $row){
        $row = $this->toParams(
            $row,
            [
                'action-type-id' => 'actionTypeId',
                'ip-address'     => 'ipAddress'
            ]
        );

        $sql = <<<SQL
INSERT INTO "Statistics"."Action"
    ("IDAction", "ActionTypeID", "IpAddress", "DateTime")
VALUES
    (DEFAULT, :actionTypeId, :ipAddress, DEFAULT)
RETURNING
    "IDAction";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }
}
?>