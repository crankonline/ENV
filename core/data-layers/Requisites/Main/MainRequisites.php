<?php

namespace Environment\DataLayers\Requisites\Main;

class MainRequisites extends \Unikum\Core\DataLayer {
    const DEFAULT_CONNECTION = 'Requisites';

    public function __construct($dbms = null){
        parent::__construct($dbms ?: self::DEFAULT_CONNECTION);
    }

    public function getByUid($uid){

        $values['uid'] = $uid;
        $params = "";

        $sql = <<<SQL
SELECT
    *
FROM
    "Common"."Requisites" as "c-r"
    LEFT JOIN "Uid"."Uid" as "u-u" ON "c-r"."UidID" = "u-u"."IDUid"
WHERE
    ("u-u"."Value" = :uid)
AND ("c-r"."IsActive" = true);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetch();
    }

    public function getByReqId($reqId) {
        $values['reqId'] = $reqId;
        $params = "";

        $sql = <<<SQL
SELECT
    *
FROM
    "Common"."Requisites" as "c-r"
WHERE
    ("c-r"."IDRequisites" = :reqId);
SQL;

        $stmt = $this->dbms->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetch();
    }

}
