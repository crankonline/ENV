<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class SochiExporters extends \Environment\Core\Module {

	protected $config = [
		'template' => 'layouts/SochiExporters/Default.html',
		'listen'   => 'action'
	];

    private function getExporters() {
        $sql = <<<SQL
            SELECT * FROM "misc"."exporters" ORDER BY 1;
SQL;
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function main() {
		$this->variables->errors = [];
        $this->variables->exporters = $this->getExporters();
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-representatives-search.css';


	}
}