<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ReportingForms extends \Environment\Core\Module {

	protected $config = [
		'template' => 'layouts/ReportingForms/Default.html',
		'listen'   => 'action'
	];

    public function getFormsSF() {

        $sql  = <<<SQL
SELECT
    "f"."name" as "name",
    "f"."description" as "description",
    "f"."valid" as "valid",
    "f"."sys_name" as "sys_name"
FROM
    "sf_reporting"."forms" as "f"

SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
    }

    public function getFormsStat() {

        $sql  = <<<SQL
SELECT
    "f"."form_name" as "form_name",
    "f"."description" as "description",
    "f"."status" as "status",
    "f"."sys_name" as "sys_name"
FROM
    "stat_reporting"."forms" as "f"

SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
    }

    public function getFormsSti() {

        $sql  = <<<SQL
SELECT
    "f"."form_name" as "form_name",
    "f"."description" as "description",
    "f"."status" as "status",
    "f"."sys_name" as "sys_name"
FROM
    "sti_reporting"."forms" as "f"

SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();

        echo json_encode($stmt->fetchAll());exit;
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
        $stmt->execute([
            'status' => $status,
            'sys_name' => $sys_name
        ]);

        echo json_encode($stmt->execute([
            'valid' => $status,
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
        $stmt->execute([
            'status' => $status,
            'sys_name' => $sys_name
        ]);

        echo json_encode($stmt->execute([
            'valid' => $status,
            'sys_name' => $sys_name
        ]));exit;
    }

	protected function main() {
		$this->variables->errors = [];

	}
}