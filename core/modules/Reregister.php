<?php
/**
 * Reregister
 */

namespace Environment\Modules;

use Environment\Soap\Clients as SoapClients,
	Environment\Soap\Types\Requisites\Data as Types;

use Environment\DataLayers\Reregister\Core as CoreSchema,
	Environment\DataLayers\Reregister\Statistics as StatisticsSchema;

class Reregister extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/Reregister/Default.html',
		'listen'   => 'action',
		'skipMain' => false
	];

	public function submit() {
		header( 'Content-Type: application/json' );

		try {
			$uid = empty( $_POST['uid'] ) ? null : $_POST['uid'];

			$requisites = Types\Import\Data::create( $_POST );

			$dataClient = new SoapClients\Requisites\Data();

			$dlSync    = new CoreSchema\Sync();
			$dlActions = new StatisticsSchema\Actions();

			$actionRow = [
				'ip-address' => $_SERVER['REMOTE_ADDR']
			];

			if ( $uid ) {
				$dataClient->update( API_SUBSCRIBER_TOKEN, $uid, $requisites );

				$actionRow['action-type-id'] = 2;
			} else {
				$uid = $dataClient->register( API_SUBSCRIBER_TOKEN, $requisites );

				$actionRow['action-type-id'] = 1;
			}

			$dlActions->register( $actionRow );

			$dlSync->setPending( $uid );

			$result = [
				'success' => true,
				'uid'     => $uid
			];
		} catch ( \SoapFault $e ) {
			\Sentry\captureException( $e );
			$result = [
				'success'       => false,
				'error-code'    => 'SOAP:' . $e->faultcode,
				'error-message' => $e->getMessage()
			];
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$result = [
				'success'       => false,
				'error-code'    => $e->getCode(),
				'error-message' => $e->getMessage()
			];
		}

		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode( $result ) ); //чтоб не подгружался вышестоящий темплейт.
	}

	protected function main() {

		if ( $this->config->skipMain ) {
			return;
		}

		$this->variables->errors = [];

		try {
			$client = new SoapClients\Requisites\Meta();

			$this->variables->data = [
				'common-bank'                    => $client->getCommonBanks( API_SUBSCRIBER_TOKEN ),
				'common-legal-form'              => $client->getCommonLegalForms( API_SUBSCRIBER_TOKEN ),
				'common-management-form'         => $client->getCommonManagementForms( API_SUBSCRIBER_TOKEN ),
				'common-ownership-form'          => $client->getCommonOwnershipForms( API_SUBSCRIBER_TOKEN ),
				'common-civil-legal-status'      => $client->getCommonCivilLegalStatuses( API_SUBSCRIBER_TOKEN ),
				'common-capital-form'            => $client->getCommonCapitalForms( API_SUBSCRIBER_TOKEN ),
				'common-region'                  => $client->getCommonRegions( API_SUBSCRIBER_TOKEN ),
				'common-district'                => $client->getCommonDistricts( API_SUBSCRIBER_TOKEN ),
				'common-eds-usage-model'         => $client->getCommonEdsUsageModels( API_SUBSCRIBER_TOKEN ),
				'common-representative-role'     => $client->getCommonRepresentativeRoles( API_SUBSCRIBER_TOKEN ),
				'common-representative-position' => $client->getCommonRepresentativePositions( API_SUBSCRIBER_TOKEN ),
				'common-chief-basis'             => $client->getCommonChiefBasises( API_SUBSCRIBER_TOKEN ),

				'sf-tariff' => $client->getSfTariffs( API_SUBSCRIBER_TOKEN ),
				'sf-region' => $client->getSfRegions( API_SUBSCRIBER_TOKEN ),

				'sti-region' => $client->getStiRegions( API_SUBSCRIBER_TOKEN )
			];
		} catch(\SoapFault $f) {
            \Sentry\captureException( $f );
            $this->variables->errors[] = $f->faultstring;
        } catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}
}

?>