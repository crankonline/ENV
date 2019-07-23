<?php

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;

class PinInfo extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/PinInfo/Default.html',
		'listen'   => 'action'
	];

	protected function getByPin( $pin ) {
		try {
			$client = new SoapClients\Sf\PinInfoService();
			$args   = new \stdClass();

			$args->pin = $pin;

			$result = $client->GetPinInfo( $args )->GetPinInfoResult;
		} catch ( \SoapFault $f ) {
			\Sentry\captureException( $f );
			throw new \Exception( $f->faultstring );
		}

		return ! $result || ( $result->Code == 'NOT_FOUND' ) ? null : $result;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-pin-info.css';

		$this->variables->errors = [];

		$pin = isset( $_GET['pin'] ) ? $_GET['pin'] : null;

		$this->variables->cPin = $pin;

		if ( empty( $pin ) ) {
			return;
		}

		try {
			$this->variables->data = $this->getByPin( $pin );
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>