<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ApiCall extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/ApiCall/Default.html'
    ];

    protected function getCall($id){
        $sql = <<<SQL
SELECT
    "a-smc"."IDServiceMethodCall" as "id",
    "a-smc"."ServiceMethodCallID" as "parent-id",
    "s-s"."Name" as "subscriber",
    CONCAT_WS('::', "a-s"."Name", "a-sm"."Name" || '(...)') as "service-method",
    CASE
        WHEN "a-smcr"."IDServiceMethodCallResult" IS NOT NULL
            THEN TO_CHAR("a-smcr"."DateTime" - "a-smc"."DateTime", 'DD, HH24:MI:SS (US)')
        WHEN "a-smce"."IDServiceMethodCallException" IS NOT NULL
            THEN TO_CHAR("a-smce"."DateTime" - "a-smc"."DateTime", 'DD, HH24:MI:SS (US)')
        ELSE NULL
    END AS "duration",
    TO_CHAR("a-smc"."DateTime", 'DD.MM.YYYY HH24:MI:SS (US)') as "stamp",
    "a-smcr"."IDServiceMethodCallResult" IS NOT NULL as "is-success",
    TO_CHAR("a-smcr"."DateTime", 'DD.MM.YYYY HH24:MI:SS (US)') as "result-stamp",
    "a-smcr"."Value" as "result-value",
    "a-smce"."IDServiceMethodCallException" IS NOT NULL as "is-failure",
    TO_CHAR("a-smce"."DateTime", 'DD.MM.YYYY HH24:MI:SS (US)') as "exception-stamp",
    "a-smce"."Code" as "exception-code",
    "a-smce"."Message" as "exception-message"
FROM
    "Api"."ServiceMethodCall" as "a-smc"
        INNER JOIN "Subscriber"."Subscriber" as "s-s"
            ON "a-smc"."SubscriberID" = "s-s"."IDSubscriber"
        INNER JOIN "Api"."ServiceMethod" as "a-sm"
            ON "a-smc"."ServiceMethodID" = "a-sm"."IDServiceMethod"
        INNER JOIN "Api"."Service" as "a-s"
            ON "a-sm"."ServiceID" = "a-s"."IDService"
        LEFT JOIN "Api"."ServiceMethodCallResult" as "a-smcr"
            ON "a-smc"."IDServiceMethodCall" = "a-smcr"."ServiceMethodCallID"
        LEFT JOIN "Api"."ServiceMethodCallException" as "a-smce"
            ON "a-smc"."IDServiceMethodCall" = "a-smce"."ServiceMethodCallID"
WHERE
    ("a-smc"."IDServiceMethodCall" = :id);
SQL;

        $stmt = Connections::getConnection('Api')->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    protected function getCallArguments($id){
        $sql = <<<SQL
SELECT
    "IDServiceMethodCallArg" as "id",
    "Value" as "value"
FROM
    "Api"."ServiceMethodCallArg" as "a-smca"
WHERE
    ("a-smca"."ServiceMethodCallID" = :callId)
ORDER BY
    1;
SQL;

        $stmt = Connections::getConnection('Api')->prepare($sql);

        $stmt->execute([
            'callId' => $id
        ]);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->view = static::AK_API_CALLS;

        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-form-colored.css';

        $this->variables->errors = [];

        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            $this->variables->errors[] = 'Вызов не задан.';
            return;
        }

        try {
            $this->variables->call = $this->getCall($id);
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении информации о вызове.';
        }

        try {
            $this->variables->arguments = $this->getCallArguments($id);
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении списка аргументов вызова.';
        }
    }
}
?>