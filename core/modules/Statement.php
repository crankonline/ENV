<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
	Environment\DataLayers\OnlineStatements\Statements as StatementsSchema,
	Environment\DataLayers\OnlineStatements\Dealers as DealersSchema,
	Environment\DataLayers\OnlineStatementFiles\Files as FilesSchema;

use Environment\Soap\Clients as SoapClients;

class Statement extends \Environment\Core\Module {
	const
		PMS_VIEW_FILES = 'can-view-files',
		PMS_VIEW_PINS = 'can-view-pins',
		PMS_VIEW_PAYMENTS = 'can-view-payments',
		PMS_APPROVE = 'can-approve',
		PMS_IDENTIFY = 'can-identify',
		PMS_CONFIRM_PAYMENT = 'can-confirm-payment',
		PMS_COMPLETE = 'can-complete',
		PMS_REJECT = 'can-reject',
		PMS_REMOVE = 'can-remove';

	const
		STAGE_IS_REVISION = 'is-revision',
		STAGE_IS_REVISED = 'is-revised',
		STAGE_IS_IDENTIFICATION = 'is-identification',
		STAGE_IS_IDENTIFIED = 'is-identified',
		STAGE_IS_PAYABLE = 'is-payable',
		STAGE_IS_PAID = 'is-paid',
		STAGE_IS_COMPLETE = 'is-complete',
		STAGE_IS_REJECTED = 'is-rejected';

	const
		ACTION_APPROVE = 'act-approve',
		ACTION_IDENTIFY = 'act-identify',
		ACTION_CONFIRM_PAYMENT = 'act-confirm-payment',
		ACTION_COMPLETE = 'act-complete',
		ACTION_REJECT = 'act-reject',
		ACTION_REMOVE = 'act-remove';

	const
		URL_FRONT = 'https://reg.dostek.kg/';

	protected $config = [
		'template' => 'layouts/Statement/Default.html'
	];

	protected function detectAbilities() {
		$abilities = [
			self::PMS_VIEW_FILES,
			self::PMS_VIEW_PINS,
			self::PMS_VIEW_PAYMENTS,
			self::PMS_APPROVE,
			self::PMS_IDENTIFY,
			self::PMS_CONFIRM_PAYMENT,
			self::PMS_COMPLETE,
			self::PMS_REJECT,
			self::PMS_REMOVE
		];

		foreach ( $abilities as $key => $ability ) {
			unset( $abilities[ $key ] );

			$abilities[ $ability ] = $this->isPermitted( self::AK_STATEMENT, $ability );
		}

		return $abilities;
	}

	protected function detectProcessingStage( $statement ) {
		$stages = [
			self::STAGE_IS_REVISION       => StatementsSchema\Statuses::REVISION,
			self::STAGE_IS_REVISED        => StatementsSchema\Statuses::REVISED,
			self::STAGE_IS_IDENTIFICATION => StatementsSchema\Statuses::IDENTIFICATION,
			self::STAGE_IS_IDENTIFIED     => StatementsSchema\Statuses::IDENTIFIED,
			self::STAGE_IS_PAYABLE        => StatementsSchema\Statuses::PAYABLE,
			self::STAGE_IS_PAID           => StatementsSchema\Statuses::PAID,
			self::STAGE_IS_COMPLETE       => StatementsSchema\Statuses::COMPLETE,
			self::STAGE_IS_REJECTED       => StatementsSchema\Statuses::REJECTED
		];

		$statementStatus = $statement['status-id'];

		foreach ( $stages as $stage => $stageStatus ) {
			$stages[ $stage ] = $stageStatus == $statementStatus;
		}

		return $stages;
	}

	protected function detectAvailibleActions( array $stages, array $abilities ) {
		$actions = [];

		if ( $stages[ self::STAGE_IS_REVISION ] && $abilities[ self::PMS_APPROVE ] ) {
			$actions[] = self::ACTION_APPROVE;

			if ( $abilities[ self::PMS_REJECT ] ) {
				$actions[] = self::ACTION_REJECT;
			}
		} elseif ( $stages[ self::STAGE_IS_IDENTIFICATION ] && $abilities[ self::PMS_IDENTIFY ] ) {
			$actions[] = self::ACTION_IDENTIFY;

			if ( $abilities[ self::PMS_REJECT ] ) {
				$actions[] = self::ACTION_REJECT;
			}
		} elseif ( $stages[ self::STAGE_IS_PAYABLE ] && $abilities[ self::PMS_CONFIRM_PAYMENT ] ) {
			$actions[] = self::ACTION_CONFIRM_PAYMENT;

			if ( $abilities[ self::PMS_REJECT ] ) {
				$actions[] = self::ACTION_REJECT;
			}
		} elseif ( $stages[ self::STAGE_IS_PAID ] && $abilities[ self::PMS_COMPLETE ] ) {
			$actions[] = self::ACTION_COMPLETE;

			if ( $abilities[ self::PMS_REJECT ] ) {
				$actions[] = self::ACTION_REJECT;
			}
		}

		if ( $abilities[ self::PMS_REMOVE ] ) {
			$actions[] = self::ACTION_REMOVE;
		}

		return $actions;
	}

	protected function setOperationStatus( $result, $status ) {
		$this->variables->result = $result;
		$this->variables->status = $status;

		return $result;
	}

	protected function finishStage( array $stage ) {
		$dbms = Connections::getConnection( 'OnlineStatements' );

		$dlStatementStatuses = new StatementsSchema\StatementStatuses( $dbms );

		try {
			$dbms->beginTransaction();

			foreach ( $stage as &$status ) {
				$dlStatementStatuses->insert( $status );
			}

			$dbms->commit();
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$dbms->rollBack();

			throw $e;
		}
	}

	protected function getUserName( array $user ) {
		return implode(
			' ',
			array_filter( [
				$user['surname'],
				$user['name'],
				$user['middle-name']
			] )
		);
	}

	protected function remove( $statementId ) {
		$dbmsStatements     = Connections::getConnection( 'OnlineStatements' );
		$dbmsStatementFiles = Connections::getConnection( 'OnlineStatementFiles' );

		$dlStatements = new StatementsSchema\Statements( $dbmsStatements );
		$dlFiles      = new StatementsSchema\Files( $dbmsStatements );
		$dlStatuses   = new StatementsSchema\StatementStatuses( $dbmsStatements );

		$dlStore = new FilesSchema\Store( $dbmsStatementFiles );

		try {
			$dbmsStatements->beginTransaction();

			$files = $dlFiles->getByStatement( $statementId );

			foreach ( $files as $index => $file ) {
				$files[ $index ] = $file['store-file-id'];
			}

			$dlFiles->deleteByStatement( $statementId );
			$dlStatuses->deleteByStatement( $statementId );

			try {
				$dbmsStatementFiles->beginTransaction();

				$dlStore->delete( $files );

				$dbmsStatementFiles->commit();
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$dbmsStatementFiles->rollBack();

				throw $e;
			}

			$dlStatements->delete( $statementId );

			$dbmsStatements->commit();
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$dbmsStatements->rollBack();

			throw $e;
		}
	}

	protected function process( $id ) {
		$action      = isset( $_POST['action'] ) ? $_POST['action'] : null;
		$description = empty( $_POST['description'] ) ? null : $_POST['description'];

		if ( ! $action ) {
			return;
		}

		$dlStatements = new StatementsSchema\Statements();

		try {
			$statement = $dlStatements->getById( $id );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );

			return $this->setOperationStatus(
				false,
				'Произошла ошибка при получении данных заявки.'
			);
		}

		if ( ! $statement ) {
			return;
		}

		$user = &$_SESSION[ SESSION_USER_KEY ];

		$operator  = $this->getUserName( $user );
		$abilities = $this->detectAbilities();
		$stages    = $this->detectProcessingStage( $statement );
		$actions   = $this->detectAvailibleActions( $stages, $abilities );

		if ( ! in_array( $action, $actions ) ) {
			return $this->setOperationStatus(
				false,
				'Запрашиваемое действие недоступно, либо неприменимо к заявке на данном этапе.'
			);
		}

		try {
			switch ( $action ) {
				case self::ACTION_APPROVE:
					$this->finishStage( [
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::REVISED,
							'description'  => $description,
							'operator'     => $operator
						],
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::IDENTIFICATION,
							'description'  => null,
							'operator'     => null
						]
					] );
					break;

				case self::ACTION_IDENTIFY:
					$this->finishStage( [
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::IDENTIFIED,
							'description'  => $description,
							'operator'     => $operator
						],
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::PAYABLE,
							'description'  => null,
							'operator'     => null
						]
					] );
					break;

				case self::ACTION_CONFIRM_PAYMENT:
					$this->finishStage( [
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::PAID,
							'description'  => $description,
							'operator'     => $operator
						]
					] );
					break;

				case self::ACTION_COMPLETE:
					$this->finishStage( [
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::COMPLETE,
							'description'  => $description,
							'operator'     => $operator
						]
					] );
					break;

				case self::ACTION_REJECT:
					$this->finishStage( [
						[
							'statement-id' => $statement['id'],
							'status-id'    => StatementsSchema\Statuses::REJECTED,
							'description'  => $description,
							'operator'     => $operator
						]
					] );
					break;

				case self::ACTION_REMOVE:
					$this->remove( $statement['id'] );
					break;
			}

			return $this->setOperationStatus( true, 'Действие выполнено.' );
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );

			return $this->setOperationStatus(
				false,
				'Произошла ошибка при осуществлении действия.'
			);
		}
	}

	protected function getFile() {
		$this->variables->errors = [];

		try {
			$id = empty( $_GET['file'] ) ? null : $_GET['file'];

			if ( ! $id ) {
				throw new \Exception( 'Файл не указан.' );
			}

			if ( ! $this->isPermitted( self::AK_STATEMENT, self::PMS_VIEW_FILES ) ) {
				throw new \Exception( 'У Вас недостаточно привилегий для доступа к файлам.' );
			}

			$dlStore = new FilesSchema\Store();

			$file = $dlStore->getById( $id );

			if ( ! $file ) {
				throw new \Exception( 'Файл не найден.' );
			}

			$this->suppress();

			while ( ob_get_level() ) {
				ob_end_clean();
			}

            if (filter_var($file['content'], FILTER_VALIDATE_URL)) {
                $file['content'] = file_get_contents($file['content']); $isNotBase64File = true;
            }

            $alias = addslashes( $file['name'] );
            header( 'Content-Length: ' . $file['size'] );
            header( 'Accept-Ranges: bytes' );
            header( 'Connection: close' );
            header( 'Content-Type: image/jpeg' );
            header( 'Content-Disposition: inline; filename="' . $alias . '"' );
            echo isset($isNotBase64File) ? $file['content'] : base64_decode( $file['content'] );
            exit;

		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Про получении файла произошла ошибка БД.';
		} catch ( \Exception $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = $e->getMessage();
		}
	}

	protected function getInvoice( $statement ) {
		$cUrl   = curl_init();
		$target = self::URL_FRONT . '?page=statementinfo';

		curl_setopt_array(
			$cUrl,
			[
				CURLOPT_URL            => $target,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => [
					'inn'      => $statement['inn'],
					'password' => $statement['password']
				]
			]
		);

		$html = curl_exec( $cUrl );

		if ( ! $html ) {
			return;
		}

		$doc = new \DOMDocument( '1.0', 'utf-8' );

		@$doc->loadHTML( $html );

		$form   = $doc->getElementsByTagName( 'form' )->item( 1 );
		$action = $form->getAttribute( 'action' );
		$input  = $form->childNodes->item( 1 );

		curl_setopt_array(
			$cUrl,
			[
				CURLOPT_URL            => self::URL_FRONT . $action,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_POST           => true,
				CURLOPT_REFERER        => $target,
				CURLOPT_POSTFIELDS     => [
					'v' => $input->getAttribute( 'value' )
				]
			]
		);

		$pdf = curl_exec( $cUrl );

		if ( ! $pdf ) {
			return;
		}

		curl_close( $cUrl );

		$this->suppress();

		header( 'Content-Type: application/pdf' );
		echo $pdf;
		exit;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-statement.css';

		if ( empty( $this->variables->errors ) ) {
			$this->variables->errors = [];
		}

		$id = isset( $_GET['id'] ) ? abs( (int) $_GET['id'] ) : null;

		if ( ! $id ) {
			$this->variables->errors[] = 'Заявка не указана.';

			return;
		}

		if ( $_POST ) {
			$this->process( $id );
		} elseif ( isset( $_GET['file'] ) ) {
			$this->getFile();
		}

		$dlStatements        = new StatementsSchema\Statements();
		$dlStatementStatuses = new StatementsSchema\StatementStatuses();

		$dlPayments     = new DealersSchema\Payments();
		$dlPaymentItems = new DealersSchema\PaymentItems();

		$dlFiles = new StatementsSchema\Files();

		try {
			$statement = $dlStatements->getById( $id );

			$this->variables->statement = &$statement;
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении данных заявки.';

			return;
		}

		if ( ! $statement ) {
			$this->variables->errors[] = 'Заявка не найдена или удалена.';

			return;
		}

		if ( ! empty( $_GET['invoice'] ) ) {
			$this->getInvoice( $statement );
		}

		$abilities = $this->detectAbilities();

		$isProcessed = in_array(
			$statement['status-id'],
			[ StatementsSchema\Statuses::REJECTED, StatementsSchema\Statuses::COMPLETE ]
		);

		$isPermitted = (
			( $this->isPermitted( self::AK_STATEMENTS_RECEIVED ) && ! $isProcessed )
			||
			( $this->isPermitted( self::AK_STATEMENTS_PROCESSED ) && $isProcessed )
		);

		if ( ! $isPermitted ) {
			$this->variables->errors[] = 'У Вас недостаточно привилегий для доступа к заявке.';

			return;
		}

		$this->context->view = $isProcessed
			? static::AK_STATEMENTS_PROCESSED
			: static::AK_STATEMENTS_RECEIVED;

		$json = json_decode( $statement['data'] );

		unset( $statement['data'] );

		if ( ! $json ) {
			$this->variables->errors[] = 'Заявка имеет непригодный для использования формат.';

			return;
		}

		try {
			$client = new SoapClients\Api\RequisitesMeta();

			if ( ! empty( $json->main ) ) {
				$json->main->ownerform = $client->getCommonOwnershipFormById(
					$client::SUBSCRIBER_TOKEN,
					$json->main->ownerform
				);

				$json->main->legalform = $client->getCommonLegalFormById(
					$client::SUBSCRIBER_TOKEN,
					$json->main->legalform
				);

				$json->main->civilstatus = $client->getCommonCivilLegalStatusById(
					$client::SUBSCRIBER_TOKEN,
					$json->main->civilstatus
				);

				if ( ! empty( $json->main->capitalform ) ) {
					$json->main->capitalform = $client->getCommonCapitalFormById(
						$client::SUBSCRIBER_TOKEN,
						$json->main->capitalform
					);
				}

				if ( ! empty( $json->main->manageform ) ) {
					$json->main->manageform = $client->getCommonManagementFormById(
						$client::SUBSCRIBER_TOKEN,
						$json->main->manageform
					);
				}
			}

			if ( ! empty( $json->person ) ) {
				$persons = [
					$json->person->chief,
					$json->person->accountant ?? null
				];

				foreach ( $persons as $person ) {
					if ( ! empty( $person->position ) ) {
						$person->position = $client->getCommonRepresentativePositionById(
							$client::SUBSCRIBER_TOKEN,
							$person->position
						);
					}

					if ( ! empty( $person->basis ) ) {
						$person->basis = $client->getCommonChiefBasisById(
							$client::SUBSCRIBER_TOKEN,
							$person->basis
						);
					}
				}
			}

			if ( ! empty( $json->reporting ) ) {
				$json->reporting->sftariff = $client->getSfTariffById(
					$client::SUBSCRIBER_TOKEN,
					$json->reporting->sftariff
				);

				$json->reporting->sfregion = $client->getSfRegionById(
					$client::SUBSCRIBER_TOKEN,
					$json->reporting->sfregion
				);

				$json->reporting->stiregion = $client->getStiRegionById(
					$client::SUBSCRIBER_TOKEN,
					$json->reporting->stiregion
				);

				$json->reporting->stiapplyingregion = $client->getStiRegionById(
					$client::SUBSCRIBER_TOKEN,
					$json->reporting->stiapplyingregion
				);
			}
		} catch ( \SoapFault $f ) {
			\Sentry\captureException( $f );
			$this->variables->errors[] = 'Произошла ошибка при осуществлении запроса к справочникам службы реквизитов.';

			return;
		}

		$this->variables->json = &$json;

		try {
			$statuses = $dlStatementStatuses->getBy( [
				'statement-id' => $id
			] );

			foreach ( $statuses as &$status ) {
				switch ( $status['id'] ) {
					case StatementsSchema\Statuses::REVISION:
					case StatementsSchema\Statuses::IDENTIFICATION:
					case StatementsSchema\Statuses::PAYABLE:
						$status['class'] = 'yellow';
						break;

					case StatementsSchema\Statuses::REVISED:
					case StatementsSchema\Statuses::IDENTIFIED:
					case StatementsSchema\Statuses::PAID:
					case StatementsSchema\Statuses::COMPLETE:
						$status['class'] = 'green';
						break;

					case StatementsSchema\Statuses::REJECTED:
						$status['class'] = 'red';
						break;

					default:
						$status['class'] = null;
						break;
				}
			}

			$this->variables->statuses = &$statuses;
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка заявок.';
		}

		if ( $abilities[ self::PMS_VIEW_FILES ] ) {
			try {
				$this->variables->files = $dlFiles->getByStatement( $statement['id'] );
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$this->variables->errors[] = 'Произошла ошибка при получении списка заявок.';
			}
		}

		if ( $abilities[ self::PMS_VIEW_PAYMENTS ] ) {
			try {
				$payment = $dlPayments->getBy( [
					'inn' => $statement['inn']
				] );

				if ( $payment ) {
					$payment = $payment[0];

					$payment['items'] = $dlPaymentItems->getBy( [
						'payment-id' => $payment['id']
					] );
				}

				$this->variables->payment = &$payment;
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$this->variables->errors[] = 'Произошла ошибка при получении данных о предоплате.';
			}
		}

		$stages  = $this->detectProcessingStage( $statement );
		$actions = $this->detectAvailibleActions( $stages, $abilities );

		$this->variables->actions   = &$actions;
		$this->variables->abilities = &$abilities;

		$this->variables->actionsMap = [
			self::ACTION_APPROVE         => [
				'title'  => 'Утверждение заявки',
				'button' => 'Утвердить',
				'class'  => 'good'
			],
			self::ACTION_IDENTIFY        => [
				'title'  => 'Идентификация пользователей заявки',
				'button' => 'Идентифицировать',
				'class'  => 'good'
			],
			self::ACTION_CONFIRM_PAYMENT => [
				'title'  => 'Подтверждение оплаты по заявке',
				'button' => 'Подтвердить оплату',
				'class'  => 'good'
			],
			self::ACTION_COMPLETE        => [
				'title'  => 'Завершение заявки',
				'button' => 'Завершить',
				'class'  => 'good'
			],
			self::ACTION_REJECT          => [
				'title'  => 'Отклонение заявки',
				'button' => 'Отклонить',
				'class'  => 'bad'
			],
			self::ACTION_REMOVE          => [
				'title'  => 'Удаление заявки',
				'button' => 'Удалить',
				'class'  => 'bad'
			]
		];
	}
}

?>