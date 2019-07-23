<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

use Environment\Soap\Clients as SoapClients;

class UinParser extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/UinParser/Default.html',
		'listen'   => 'action'
	];

	protected static function parse( $uin ) {
		if ( ! preg_match( '/^\d{49}$/', $uin ) ) {
			throw new \Exception( 'Идентификатор отчета должен состоять из 49 цифр' );
		}

		return [
			'UIN'        => $uin,
			'Year'       => substr( $uin, 0, 4 ),
			'Month'      => substr( $uin, 4, 2 ),
			'Day'        => substr( $uin, 6, 2 ),
			'Hour'       => substr( $uin, 8, 2 ),
			'Minute'     => substr( $uin, 10, 2 ),
			'Second'     => substr( $uin, 12, 2 ),
			'UID'        => substr( $uin, 14, 23 ),
			'Subscriber' => substr( $uin, 37, 3 ),
			'Number'     => substr( $uin, 40 )
		];
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-uin-parser.css';

		$this->variables->errors = [];

		$uin = isset( $_GET['uin'] ) ? $_GET['uin'] : null;

		$this->variables->cUin = $uin;

		if ( empty( $uin ) ) {
			return;
		}

		try {
			$this->variables->data = $this->parse( $uin );
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>