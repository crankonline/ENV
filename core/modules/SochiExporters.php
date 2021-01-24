<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class SochiExporters extends \Environment\Core\Module {

	protected $config = [
		'template' => 'layouts/SochiExporters/Default.html',
		'listen'   => 'action'
	];

    private function getExporters():array {
        $sql = <<<SQL
            SELECT * FROM "misc"."exporters" ORDER BY 1;
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getLog(int $exporter, int $status):array {


        $filters = [];

        if($exporter > 0) {
            $params['exporter_id'] = $exporter;
            $filters[] = 'exporter_id = :exporter_id';
        }

        if($status > 0) {
            $params['status'] = $status === 1 ? 't' : 'f';
            $filters[] = 'status = :status';
        }

        if(count($params) > 0)
            $filters = 'WHERE ' . implode(' AND ', $filters);


        $sql = <<<SQL
            SELECT
                "log"."id",
                "log"."status",
                "log"."message",
                "log"."date_time",
                "misc"."exporters"."exporter" 
            FROM
                "misc"."exporter_log" AS "log"
                INNER JOIN "misc"."exporters" ON "log"."exporter_id" = "misc"."exporters"."id"
            {$filters}
            ORDER BY
                "log"."id" DESC
                LIMIT 100 OFFSET 0;
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute($params);
        return $stmt->fetchAll();

    }


    protected function main() {

        $this->variables->cExporter = $_GET['exporter'] ?? 0;
        $this->variables->cStatus = $_GET['status'] ?? 0;

		$this->variables->errors = [];
        $this->variables->exporters = $this->getExporters();
        $this->variables->log = $this->getLog(
            $_GET['exporter'] ?? 0,
            $_GET['status'] ?? 0
        );
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-representatives-search.css';
	}
}