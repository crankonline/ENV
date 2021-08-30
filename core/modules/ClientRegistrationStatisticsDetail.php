<?php


namespace Environment\Modules;

use Environment\Modules\ClientRegistrationStatistics as ClRSt;
use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\DataLayers\Requisites\Main as RequisitesModel;


class ClientRegistrationStatisticsDetail extends \Environment\Core\Module {

    protected $config = [
        'template' => 'layouts/ClientRegistrationStatisticsDetail/Default.php',
        'listen'   => 'action'
    ];

    public function getRecordsIp( $periodFrom, $periodTo, $ip ) {
        $sql = <<<SQL
SELECT
    HOST("s-a"."IpAddress") as "ip-address",
    TO_CHAR("s-a"."DateTime", 'DD.MM.YYYY') as "date",
    "s-a"."UserID" as "userId",
    "s-a"."RequisitesID" as "reqId",
    "s-a"."ActionTypeID" as "action"
FROM
    "Statistics"."Action" as "s-a"
        INNER JOIN "Statistics"."ActionType" as "s-at"
            ON "s-a"."ActionTypeID" = "s-at"."IDActionType"
WHERE
    ("s-a"."DateTime"::DATE BETWEEN :periodFrom AND :periodTo)
    AND "s-a"."IpAddress" = :ip
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute( [
            'periodFrom' => $periodFrom,
            'periodTo'   => $periodTo,
            'ip'         => $ip
        ] );

        return $stmt;
    }

    public function getRecordsUserId( $periodFrom, $periodTo, $userId ) {
        $sql = <<<SQL
SELECT
    HOST("s-a"."IpAddress") as "ip-address",
    TO_CHAR("s-a"."DateTime", 'DD.MM.YYYY') as "date",
    "s-a"."UserID" as "userId",
    "s-a"."RequisitesID" as "reqId",
    "s-a"."ActionTypeID" as "action"
FROM
    "Statistics"."Action" as "s-a"
        INNER JOIN "Statistics"."ActionType" as "s-at"
            ON "s-a"."ActionTypeID" = "s-at"."IDActionType"
WHERE
    ("s-a"."DateTime"::DATE BETWEEN :periodFrom AND :periodTo)
    AND "s-a"."UserID" = :userId
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );

        $stmt->execute( [
            'periodFrom' => $periodFrom,
            'periodTo'   => $periodTo,
            'userId'     => $userId
        ] );

        return $stmt;
    }

    public function getRequisites(){
        if(isset($_GET['req'])) {
            $reqId = $_GET['req'];
            $dlReauisites = new RequisitesModel\MainRequisites();
            $req = $dlReauisites->getByReqId($reqId);
            $url = "Location: index.php?view=requisites&inn=".$req['Inn']."&uid=&date=".$req['DateTime'];
            header($url, true, 301);
            exit();
        }
    }

    private function getCountsByUserIdAndDates(int $userId, string $from, string $to):array {
        $sql = <<<SQL
            SELECT
                SUM(("s-at"."Name" = 'register')::INT) as "register-count",
                SUM(("s-at"."Name" = 'update')::INT) as "update-count"
            FROM
                "Statistics"."Action" as "s-a"
                    INNER JOIN "Statistics"."ActionType" as "s-at"
                        ON "s-a"."ActionTypeID" = "s-at"."IDActionType"
            WHERE
                ("s-a"."DateTime"::DATE BETWEEN ? AND ?)
                    AND "s-a"."UserID" = ?;
SQL;

        $stmt = Connections::getConnection( 'Reregister' )->prepare( $sql );
        $stmt->execute([ $from, $to, $userId ]);
        return $stmt->fetch();
    }

    protected function main() {

        $t = new ClRSt();
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-client-registration-statistics.css';

        $this->variables->errors = [];

        $periodFrom = $_GET['period-from'] ?? null;
        $periodTo = $_GET['period-to'] ?? date('Y-m-d');

        if( isset( $_GET['ip'] ) ) {
            $ip = $_GET['ip'];
        } elseif ($_GET['userId']) {
            $userId = $_GET['userId'];
        } else {
            exit();
        }
        if (!strtotime($periodFrom)) {
            $periodFrom = date('Y-m-') . '01';
        }

        if (!strtotime($periodTo)) {
            $periodTo = date('Y-m-d');
        }

        if(!empty($userId)) {
            $this->variables->counts = $this->getCountsByUserIdAndDates($userId, $periodFrom, $periodTo);
        }

        $this->variables->periodFrom = $periodFrom;
        $this->variables->periodTo = $periodTo;
        $this->variables->userId = $userId ?? null;
        $this->variables->t = $t;

        if (isset( $_GET['ip'])) {
            try {
                $this->variables->records = $this->getRecordsIp($periodFrom, $periodTo, $ip);
            } catch (\PDOException $e) {
                \Sentry\captureException($e);
                $this->variables->errors[] = $e->getMessage();
            }
        } elseif (isset($_GET['userId'])) {
            try {
                $this->variables->records = $this->getRecordsUserId($periodFrom, $periodTo, $userId);
            } catch (\PDOException $e) {
                \Sentry\captureException($e);
                $this->variables->errors[] = $e->getMessage();
            }
        } else {
            exit();
        }
    }
}
