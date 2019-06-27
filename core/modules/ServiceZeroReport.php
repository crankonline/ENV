<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ServiceZeroReport extends \Environment\Core\Module {
    const ZERO_REPORT_CODE = 1;

    protected $config = [
        'template'   => 'layouts/ServiceZeroReport/Default.html',
        'listen'     => 'action'
    ];

    private function getUsers() {
        $sql = <<<SQL
SELECT
    "u"."inn" AS "inn",
    "u"."uid" AS "uid",
    "u"."name" AS "name",
    "zpp"."protocol" AS "protocol"
FROM
    "options"."user_option" AS "uo"
INNER JOIN "billing"."users" AS "u" ON "uo"."user_id" = "u"."id"
LEFT JOIN (
    SELECT
        "zp"."uid",
        "zp"."protocol"
    FROM
        "options"."zero_protocol" AS "zp"
    INNER JOIN (
        SELECT
            "uid",
            MAX ("id") AS "MaxID"
        FROM
            "options"."zero_protocol"
        WHERE
            (
                EXTRACT (MONTH FROM "date_time") >= EXTRACT (MONTH FROM NOW())
                AND EXTRACT (YEAR FROM "date_time") = EXTRACT (YEAR FROM NOW())
                AND "protocol" NOT LIKE '%Успешно%'
            )
        GROUP BY
            "uid"
    ) AS "z" ON "z"."MaxID" = "zp"."id"
    AND "z"."uid" = "zp"."uid"
) AS "zpp" ON ("zpp"."uid" = "u"."uid")
WHERE
    ("uo"."option_id" = :option)
ORDER BY
    "zpp"."protocol",
    "u"."inn" DESC;
SQL;
        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute([
            'option' => self::ZERO_REPORT_CODE
        ]);

        return $stmt->fetchAll();
    }

    private function getForms($uid){
        $sql = <<<SQL
SELECT
    'СФ' as "section",
    "f"."name" as "name",
    "f"."description" as "description",
    "ur"."report_code" as "code"
FROM
    "sf_reporting"."forms" as "f"
        INNER JOIN "options"."user_report" as "ur"
            ON "f"."billing_name" = SUBSTRING("ur"."report_code", 9)
WHERE
    ("ur"."uid" = :uid)
UNION ALL
SELECT
    'ГНС' as "section",
    "f"."form_name" as "name",
    "f"."description" as "description",
    "ur"."report_code" as "code"
FROM
    "sti_reporting"."forms" as "f"
        INNER JOIN "options"."user_report" as "ur"
            ON "f"."billing_name" = SUBSTRING("ur"."report_code", 10)
WHERE
    ("ur"."uid" = :uid)
UNION ALL
SELECT
    'НСК' as "section",
    "f"."form_name" as "name",
    "f"."description" as "description",
    "ur"."report_code" as "code"
FROM
    "stat_reporting"."forms" as "f"
        INNER JOIN "options"."user_report" as "ur"
            ON "f"."billing_name" = SUBSTRING("ur"."report_code", 10)
WHERE
    ("ur"."uid" = :uid);
SQL;
        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute([
            'uid' => $uid
        ]);

        return $stmt->fetchAll();
    }

    private function getProtocol($uid){
        $sql = <<<SQL
SELECT
    "zp"."form_code" as "form",
    "zp"."date_time" as "date-time",
    "zp"."protocol" as "protocol"
FROM
    "options"."zero_protocol" as "zp"
WHERE
    ("zp"."uid" = :uid);
SQL;

        $stmt = Connections::getConnection('Sochi')->prepare($sql);

        $stmt->execute([
            'uid' => $uid
        ]);

        return $stmt->fetchAll();
    }

    private function getUidByInn($inn) {
        $connection = Connections::getConnection('Sochi');

        $sql = <<<SQL
SELECT
    "u"."uid" as "uid"
FROM
    "billing"."users" as "u"
WHERE
    ("u"."inn" = :inn);
SQL;
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            'inn' => $inn
        ]);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-sochi.css';
        $this->context->css[] = 'resources/css/ui-service-zero-report.css';

        $this->variables->errors = [];

        $inn = isset($_GET['inn']) ? $_GET['inn'] : null;
        $uid = isset($_GET['uid']) ? $_GET['uid'] : null;

        if($inn && !preg_match('/^(\d{10,10})|(\d{14,14})$/', $inn)){
            $this->variables->errors[] = 'ИНН должен состоять из 10 или 14 цифр';
            return;
        }

        if($uid && !preg_match('/^\d{23,23}$/', $uid)){
            $this->variables->errors[] = 'UID должен состоять из 23 цифр';
            return;
        }

        if($inn){
            $uid = $this->getUidByInn($inn)[0]['uid'];
        }

        $this->variables->uid = $uid;

        try {
            if($uid) {
                $this->variables->forms    = $this->getForms($uid);
                $this->variables->protocol = $this->getProtocol($uid);
            }

            $this->variables->users = $this->getUsers();
        } catch(\Exception $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
