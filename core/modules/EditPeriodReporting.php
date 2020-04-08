<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class EditPeriodReporting extends \Environment\Core\Module {

    protected $config = [
        'template' => 'layouts/EditPeriodReporting/Default.html',
        'listen'   => 'action'
    ];

    public function getFormsSFDate() {

        $sql  = <<<SQL
SELECT
    "f"."name" as "name",
    "f"."description" as "description",
    "f"."sys_name" as "sys_name",
    "f"."year" as "year",
    "f"."month" as "month",
    "f"."day" as "day"
FROM
    "sf_reporting"."forms" as "f"

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
    "f"."month" as "month",
    "f"."day" as "day"
FROM
    "stat_reporting"."forms" as "f"

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

    protected function main() {
        $this->variables->errors = [];

    }
}