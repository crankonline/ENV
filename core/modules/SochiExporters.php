<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class SochiExporters extends \Environment\Core\Module {

    private static $limit = 50;

	protected $config = [
		'template' => 'layouts/SochiExporters/Default.html',
		'listen'   => 'action',
        'plugins'  => [
            'paginator' => Plugins\Paginator::class
        ]
	];

    private function getExporters():array {
        $sql = <<<SQL
            SELECT * FROM "misc"."exporters" ORDER BY 1;
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getLog(int $exporter, int $status, int $page = 1):array {

        if($page < 1) $page = 1;

        $filters = [];

        $params = [];

        if($exporter > 0) {
            $params['exporter_id'] = $exporter;
            $filters[] = 'exporter_id = :exporter_id';
        }

        if($status > 0) {
            $params['status'] = $status === 1 ? 't' : 'f';
            $filters[] = 'status = :status';
        }

        $filters = (count($params) > 0) ? 'WHERE ' . implode(' AND ', $filters) : '';

        $limit = static::$limit;
        $offset = ($page - 1) * $limit;

        $sql = <<<SQL
            SELECT
                "log"."id",
                "log"."status",
                "log"."message",
                to_char("log"."date_time", 'DD.MM.YYYY HH24:MM:SS') AS "date_time",
                "misc"."exporters"."exporter" 
            FROM
                "misc"."exporter_log" AS "log"
                INNER JOIN "misc"."exporters" ON "log"."exporter_id" = "misc"."exporters"."id"
            {$filters}
            ORDER BY
                "log"."id" DESC
                LIMIT :limit OFFSET :offset;
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute($params + [ 'limit' => $limit, 'offset' => $offset ]);
        $rows = $stmt->fetchAll();

        $sql = <<<SQL
            SELECT
                COUNT("log"."id")
            FROM
                "misc"."exporter_log" AS "log"
                INNER JOIN "misc"."exporters" ON "log"."exporter_id" = "misc"."exporters"."id"
            {$filters};
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute($params);
        $count = $stmt->fetchColumn();

        return [
            &$count,
            &$rows,
            &$offset
        ];

    }


    protected function main() {

        $this->variables->cExporter = (int)($_GET['exporter'] ?? 0);
        $this->variables->cStatus = (int)($_GET['status'] ?? 0);
        $this->variables->cPage = (int)($_GET['page'] ?? 1);

        $this->variables->cExporter = 0;

        $this->variables->cStatus = 0;

        if($this->variables->cPage === 0)
            $this->variables->cPage = 1;

		$this->variables->errors = [];
        $this->variables->exporters = $this->getExporters();

        list($count, $log, $offset) = $this->getLog(
            $this->variables->cExporter,
            $this->variables->cStatus,
            $this->variables->cPage
        );

        $this->context->paginator['count'] = (int)ceil($count / static::$limit);

        $this->variables->count = $count;
        $this->variables->log = &$log;

        $this->variables->offset = $offset;

        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-representatives-search.css';
	}

}