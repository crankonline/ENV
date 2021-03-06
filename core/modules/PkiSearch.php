<?php

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;

class PkiSearch extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/PkiSearch/Default.html',
		'listen'   => 'action'
	];

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-pki-search.css';

		$this->variables->errors = [];

		$value = isset( $_GET['value'] ) ? $_GET['value'] : null;

		if ( ! $value ) {
			return;
		}

		$this->variables->cValue = $value;

		try {
			$clientDTG = new SoapClients\PkiService();

			$certificatesDTG = $clientDTG->search( $value );

			if ( ! is_null( $certificatesDTG ) ) {
				foreach ( $certificatesDTG as $record ) {
					$record->CA = 'DTG';
				}
			}

			$this->variables->certificates = is_null( $certificatesDTG ) ? [] : $certificatesDTG;

			// var_dump($this->variables->certificates);

			usort( $this->variables->certificates, function ( $a, $b ) {//сортировка
				return ( $b->DateFinish > $a->DateFinish );
			} );


		} catch ( \SoapFault $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->faultstring;
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>