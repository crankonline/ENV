<?php
/**
 * Reregister
 */
namespace Environment\DataLayers\Reregister\Core;

class Sync extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Reregister';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getPending($categorize = true){
        $sql = <<<SQL
SELECT
    "uid-id",
    "uid-value",
    "sync-trigger-id",
    "sync-trigger-name",
    "sync-trigger-order"
FROM
    "Core"."UidsToSync";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute();

        if(!$categorize){
            return $stmt->fetchAll();
        }

        $rows = [];

        while($row = $stmt->fetch()){
            $uid = $row['uid-value'];

            if(!isset($rows[$uid])){
                $rows[$uid] = [];
            }

            $rows[$uid][] = $row;
        }

        return $rows;
    }

    public function setPending($uid){
        $sql = <<<SQL
INSERT INTO "Core"."Uid"
    ("IDUid", "Value", "DateTime")
VALUES
    (DEFAULT, :uid, DEFAULT)
RETURNING
    "IDUid";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'uid' => $uid
        ]);

        return $stmt->fetchColumn();
    }

    public function unsetPending($uidId){
        $sql = <<<SQL
DELETE FROM
    "Core"."Uid"
WHERE
    ("IDUid" = :uidId);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute([
            'uidId' => $uidId
        ]);

        return $stmt->execute([
            'uidId' => $uidId
        ]);
    }

    public function registerCall(array $row){
        $row = $this->toParams(
            $row,
            [
                'uid-id'          => 'uidId',
                'sync-trigger-id' => 'syncTriggerId'
            ]
        );

        $sql = <<<SQL
INSERT INTO "Core"."SyncTriggerCall"
    ("IDSyncTriggerCall", "UidID", "SyncTriggerID", "DateTime")
VALUES
    (DEFAULT, :uidId, :syncTriggerId, DEFAULT)
RETURNING
    "IDSyncTriggerCall";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function registerCallResult(array $row){
        $row = $this->toParams(
            $row,
            [
                'sync-trigger-call-id' => 'syncTriggerCallId',
                'result'               => null
            ]
        );

        $row['result'] = (int)$row['result'];

        $sql = <<<SQL
INSERT INTO "Core"."SyncTriggerCallResult"
    ("IDSyncTriggerCallResult", "SyncTriggerCallID", "Result", "DateTime")
VALUES
    (DEFAULT, :syncTriggerCallId, :result, DEFAULT)
RETURNING
    "IDSyncTriggerCallResult";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }

    public function registerCallException(array $row){
        $row = $this->toParams(
            $row,
            [
                'sync-trigger-call-id' => 'syncTriggerCallId',
                'code'                 => null,
                'message'              => null
            ]
        );

        $sql = <<<SQL
INSERT INTO "Core"."SyncTriggerCallException"
    ("IDSyncTriggerCallException", "SyncTriggerCallID", "Code", "Message", "DateTime")
VALUES
    (DEFAULT, :syncTriggerCallId, :code, :message, DEFAULT)
RETURNING
    "IDSyncTriggerCallException";
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($row);

        return $stmt->fetchColumn();
    }
}
?>