<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;


class PaymentService extends \Environment\Core\Module
{

    protected $config = [
        'template' => 'layouts/PaymentService/Default.html',
        'listen' => 'action'
    ];



    function  countSys(){
        $sql  = <<<SQL
SELECT
    max("p"."IDPaymentSystem")
FROM
     "Payment"."PaymentSystem" AS "p"
SQL;
        $stmt = Connections::getConnection( 'Pay' )->prepare( $sql );

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    function  chkSys($name){
        $sql  = <<<SQL
SELECT count("p"."Name")    
FROM
     "Payment"."PaymentSystem" AS "p"
WHERE "p"."Name" = :t_name     
SQL;
        $stmt = Connections::getConnection( 'Pay' )->prepare( $sql );

        $stmt->execute([
            't_name' => $name
        ]);

        return $stmt->fetchColumn();
    }

    function  chkSysIP($ip, $pay){

        $sql  = <<<SQL
SELECT count("p"."IP")    
FROM
     "Payment"."IPAddress" AS "p"
WHERE "p"."IP" = :t_ip AND "p"."PaymentSystemID" = :t_pay     
SQL;
        $stmt = Connections::getConnection( 'Pay' )->prepare( $sql );

        $stmt->execute([
            't_ip' => $ip,
            't_pay' => $pay
        ]);

        return $stmt->fetchColumn();
    }

    function  addServiceIP(){
        $resault    = file_get_contents('php://input');
        $resault    = json_decode($resault,true);

        $IDPaymentSys       = $resault['PaymentSystemID'] ?? null;
        $ip         = $resault['IP'] ?? null;

        if ($this->chkSysIP($ip, $IDPaymentSys) >= 1) {
            echo json_encode('dublicate');
            exit();
        } else {
            $sql = <<<SQL
INSERT INTO
    "Payment"."IPAddress"
("IP", "PaymentSystemID") VALUES (:t_ip, :t_id)
    RETURNING
    "IP";

SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                't_ip' => $ip,
                't_id' => $IDPaymentSys
            ]);

            $ins = $stmt->fetchAll();

            echo json_encode('success');
            exit();
        }

    }

    function  addServiceIPNw(int $IDPaymentSys, string $ip){

        if ($this->chkSysIP($ip, $IDPaymentSys) >= 1) {
            echo json_encode('dublicate_ip');
            exit();
        } else {
            $sql = <<<SQL
INSERT INTO
    "Payment"."IPAddress"
("IP", "PaymentSystemID") VALUES (:t_ip, :t_id)
    RETURNING
    "IP";

SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                't_ip' => $ip,
                't_id' => $IDPaymentSys
            ]);

            $ins = $stmt->fetchAll();

            echo json_encode('success');
            exit();
        }

    }

    public function addService() {

    $resault    = file_get_contents('php://input');
    $resault    = json_decode($resault,true);
    $name       = $resault['inpt_name'] ?? null;
    $token      = $resault['inpt-token'] ?? null;
    $ip         = $resault['inpt-ip'] ?? null;
    $count      = $this->countSys();

     if ($this->chkSys($name) >= 1) {
         echo json_encode('dublicate');
         exit();
     } else {

         $sql = <<<SQL
INSERT INTO
    "Payment"."PaymentSystem"
("IDPaymentSystem","Name", "Token") VALUES (:t_id, :t_name, :t_token)
    RETURNING
    "IDPaymentSystem";



SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute(  [
            't_id' => $count + 1,
            't_name' => $name,
            't_token' => $token
         ]);

        $ins = $stmt->fetchAll();

       $this->addServiceIPNw($ins[0]['IDPaymentSystem'], $ip);

        for ($x = 1; !empty($resault['inp-dop'.$x]); $x++) {
            $this->addServiceIPNw($ins[0]['IDPaymentSystem'], $resault['inp-dop'.$x]);
        }

        echo json_encode('success');
        exit();
      }
    }

    public function editService() {

    $resault    = file_get_contents('php://input');
    $resault    = json_decode($resault,true);
    $id_s       = $resault['IDPaymentSystem'] ?? null;
    $name       = $resault['Name'] ?? null;
    $token      = $resault['Token'] ?? null;

        $sql = <<<SQL
        UPDATE
    "Payment"."PaymentSystem"
SET
	"Name" =:t_name, 
	"Token" = :t_token
WHERE
("IDPaymentSystem" = :t_id);
SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute(  [
            't_id' => $id_s,
            't_name' => $name,
            't_token' => $token
         ]);

        $ins = $stmt->fetchAll();

        echo json_encode('success');
        exit();
    }
    public function deleteService()
    {

        $resault = file_get_contents('php://input');
        $resault = json_decode($resault, true);

        $insertService = $resault['IDPaymentSystem'] ?? null;

        $sql = <<<SQL
DELETE FROM
    "Payment"."PaymentSystem"
WHERE
    ("IDPaymentSystem" = :t_id);
SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute([
            't_id' => $insertService
        ]);

        $this->deleteServiceIP($insertService);



    }

    public function editServiceIP() {

    $resault    = file_get_contents('php://input');
    $resault    = json_decode($resault,true);
    $id_s       = $resault['PaymentSystemID'] ?? null;
    $ip         = $resault['IP'] ?? null;
    $ip_st      = $resault['IP_ST'] ?? null;

        if ($this->chkSysIP($ip, $id_s) >= 1) {
            echo json_encode('dublicate');
            exit();
        } else {

            $sql = <<<SQL
        UPDATE
    "Payment"."IPAddress"
SET
	"IP" =:t_ip
WHERE
("PaymentSystemID" = :id_s AND "IP" = :t_ip_st);
SQL;

            $stmt = Connections::getConnection('Pay')->prepare($sql);

            $stmt->execute([
                't_ip' => $ip,
                'id_s' => $id_s,
                't_ip_st' => $ip_st
            ]);

            $ins = $stmt->fetchAll();

            echo json_encode('success');
            exit();
        }
    }

    public function deleteServiceIP(int $insertService)
    {

        $sql = <<<SQL
DELETE FROM
    "Payment"."IPAddress"
WHERE
    ("PaymentSystemID" = :t_id);
SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute([
            't_id' => $insertService
        ]);

        echo json_encode('success');
        exit();

    }

    public function deleteServiceIPNw()
    {

        $resault    = file_get_contents('php://input');
        $resault    = json_decode($resault,true);
        $id_s       = $resault['PaymentSystemID'] ?? null;
        $ip         = $resault['IP'] ?? null;

        $sql = <<<SQL
DELETE FROM
    "Payment"."IPAddress"
WHERE
    ("PaymentSystemID" = :t_id AND "IP" = :t_ip);
SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);

        $stmt->execute([
            't_id' => $id_s,
            't_ip' => $ip
        ]);

        echo json_encode('success');
        exit();

    }


    private function getPaymentSystem() {
        $sql = <<<SQL
SELECT * FROM "Payment"."PaymentSystem" as "ps"
ORDER BY "ps"."IDPaymentSystem"

SQL;

        $stmt = Connections::getConnection('Pay')->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    private function getPaymentIP($idPaySys) {
        $sql = <<<SQL
SELECT * FROM "Payment"."IPAddress" AS "i"
WHERE "i"."PaymentSystemID" = '{$idPaySys}'

SQL;
        $stmt = Connections::getConnection('Pay')->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }

    protected function main() {
        $this->variables->errors = [];
        $idPaySys   = $_GET['idPaySys'] ?? null;
        $paySysName = $_GET['paySysName'] ?? null;



        try {
            $this->variables->paymentSystem = $this->getPaymentSystem();

            if ($idPaySys) {

                $this->variables->paymentSystemIP = $this->getPaymentIP($idPaySys);
                if (empty($this->getPaymentIP($idPaySys)) && isset($idPaySys)) {
                    $this->variables->paymentSystemChk = 'suc';
                }

                $this->variables->paySysName = $paySysName;
                $this->variables->idPaySys = $_GET['idPaySys'] ?? null;
            }


        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }
}