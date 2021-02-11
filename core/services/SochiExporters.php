<?php
namespace Environment\Services;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class SochiExporters extends \Unikum\Core\Module {
	protected $config = [
		'render' => false
	];

	protected function main() {
		$data = json_decode(file_get_contents('php://input'));
		$sql = <<<SQL
			UPDATE "misc"."exporters" SET
				"is_active" = ?
			WHERE "exporter" = ?;
SQL;
		
        $stmt = Connections::getConnection( 'Sochi' )->prepare( $sql );
        $stmt->execute([ $data->status ? 't' : 'f', $data->code ]);
        $stmt->fetch();
	}

}