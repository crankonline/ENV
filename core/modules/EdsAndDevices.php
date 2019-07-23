<?php

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients;

class EdsAndDevices extends \Environment\Core\Module {
	const
		ROLES_CHIEF = 1,
		ROLES_ACCOUNTANT = 2,
		ROLES_CONSULTING = 5,
		ROLES_ROOT = 6;

	protected $config = [
		'template' => 'layouts/EdsAndDevices/Default.html',
		'listen'   => 'action'
	];

	protected function getRequisites( $inn, $uid ) {
		$client = new SoapClients\Api\RequisitesData();

		$requisites = $uid
			? $client->getByUid( $client::SUBSCRIBER_TOKEN, $uid, null )
			: $client->getByInn( $client::SUBSCRIBER_TOKEN, $inn, null );

		if ( ! ( $requisites && $requisites->common ) ) {
			throw new \Exception( 'Клиент не найден' );
		}

		$consulting = null;

		foreach ( $requisites->common->representatives as $rep ) {
			foreach ( $rep->roles as $role ) {
				switch ( $role->id ) {
					case self::ROLES_CONSULTING:
						$consulting = $rep->person->passport;
						break 2;

					case self::ROLES_ROOT:
						$consulting = $rep->person->passport;
						break 2;
				}
			}
		}

		$bindings = $consulting
			? $client->getConsultingBindingsByPassport(
				$client::SUBSCRIBER_TOKEN,
				$consulting->series,
				$consulting->number
			)
			: null;

		return [ $requisites, $bindings ];
	}

	protected function getPkiCertificates( $inn ) {
		return ( new SoapClients\PkiService() )->search( $inn );
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-requisites.css';

		$this->variables->errors = [];

		$inn = isset( $_GET['inn'] ) ? $_GET['inn'] : null;

		if ( ! $inn ) {
			return;
		}

		if ( $inn && ! preg_match( '/^(\d{10,10})|(\d{14,14})$/', $inn ) ) {
			$this->variables->errors[] = 'ИНН должен состоять из 10 или 14 цифр';

			return;
		}

		try {
			list( $requisites, $bindings ) = $this->getRequisites( $inn, null );
		} catch ( \SoapFault $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->faultstring;

			return;
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();

			return;
		}

		$this->variables->requisites = $requisites;
		$this->variables->bindings   = $bindings;

		$this->variables->certificates = $this->getPkiCertificates(
			$requisites->common->inn
		);
	}
}

?>