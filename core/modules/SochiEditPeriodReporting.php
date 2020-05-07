<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class SochiEditPeriodReporting extends \Environment\Core\Module {

    protected $config = [
        'template' => 'layouts/SochiEditPeriodReporting/Default.html',
        'listen'   => 'action'
    ];

    public function getFormsSFDate() {

        $sql  = <<<SQL
SELECT
    "f"."name" as "name",
    "f"."description" as "description",
    "f"."sys_name" as "sys_name",
    "f"."valid" as "valid",
    "f"."year" as "year",
    "f"."month" as "month",
    "f"."day" as "day"
FROM
    "sf_reporting"."forms" as "f"
ORDER BY
    "f"."sys_name";

SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
    }

    public function getFormsStatDate() {

        $sql  = <<<SQL
SELECT
    "f"."form_name" as "form_name",
    "f"."description" as "description",
    "f"."sys_name" as "sys_name",
    "f"."status" as "status",
    "f"."month" as "month",
    "f"."day" as "day"
FROM
    "stat_reporting"."forms" as "f"
ORDER BY
    "f"."sys_name";
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
    }

    public function getFormsStiDate() {

        $sql  = <<<SQL
SELECT
    "f"."form_name" as "form_name",
    "f"."description" as "description",
    "f"."status" as "status",
    "f"."sys_name" as "sys_name",
    "f"."month" as "month",
    "f"."day" as "day",
    "f"."start_date" as "start_date",
    "f"."end_date" as "end_date"
FROM
    "sti_reporting"."forms" as "f"
ORDER BY
    "f"."sys_name";
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
    }


    public function updateFormsSFDate() {

         $sys_name = $_POST['sys_name'];
         $definition = $_POST['definition'];
         $val = $_POST['value'];

         if(!in_array($definition, [ 'day', 'month', 'year' ])) {
           throw new \Exception("Unsupported definition " . $definition);
         }

        $sql = <<<SQL
UPDATE
    "sf_reporting"."forms"
SET
    "{$definition}" = :val
WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

        echo json_encode($stmt->execute([

            'sys_name' => $sys_name,
            'val' => $val
        ]));exit;

    }

    public function updateFormsStatDate() {

         $sys_name = $_POST['sys_name'];
         $definition = $_POST['definition'];
         $val = $_POST['value'];

         if(!in_array($definition, [ 'day', 'month' ])) {
           throw new \Exception("Unsupported definition " . $definition);
         }

        $sql = <<<SQL
UPDATE
    "stat_reporting"."forms"
SET
    "{$definition}" = :val
WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'sys_name' => $sys_name,
            'val' => $val
        ]));exit;

    }

  public function updateFormsStiDate() {

         $sys_name = $_POST['sys_name'];
         $definition = $_POST['definition'];
         $val = $_POST['value'];

         if(!in_array($definition, [ 'day', 'month', 'start_date', 'end_date' ])) {
           throw new \Exception("Unsupported definition " . $definition);
         }

        $sql = <<<SQL
UPDATE
    "sti_reporting"."forms"
SET
    "{$definition}" = :val
WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

        echo json_encode($stmt->execute([

            'sys_name' => $sys_name,
            'val' => $val
        ]));exit;

     }

    public function updateFormsSF() {
        $sys_name = $_POST['sys_name'];
        $valid = $_POST['valid'];
        $sql = <<<SQL
UPDATE
    "sf_reporting"."forms"
SET
    "valid" = :valid

WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute([
            'valid' => $valid,
            'sys_name' => $sys_name
        ]);

        echo json_encode($stmt->execute([
            'valid' => $valid,
            'sys_name' => $sys_name
        ]));exit;
    }


    public function updateFormsStat() {
        $sys_name = $_POST['sys_name'];
        $status = $_POST['status'];
        $sql = <<<SQL
UPDATE
    "stat_reporting"."forms"
SET
    "status" = :status

WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'status' => $status,
            'sys_name' => $sys_name
        ]));exit;
    }


    public function updateFormsSti() {
        $sys_name = $_POST['sys_name'];
        $status = $_POST['status'];
        $sql = <<<SQL
UPDATE
    "sti_reporting"."forms"
SET
    "status" = :status

WHERE
    ("sys_name" = :sys_name);
SQL;

        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'status' => $status,
            'sys_name' => $sys_name
        ]));exit;
    }

    protected function main() {
        $this->variables->errors = [];

    }
}