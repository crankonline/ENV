<?php

namespace Environment\Modules;

use Environment\DataLayers\OnlineStatements\Statements as StatementsSchema;

class StatementsProcessed extends \Environment\Core\Module {
	const
		ROWS_PER_PAGE = 30;

	protected $config = [
		'template' => 'layouts/StatementsProcessed/Default.html',
		'plugins'  => [
			'paginator' => Plugins\Paginator::class
		]
	];

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-misc-stripes.css';
		$this->context->css[] = 'resources/css/ui-statements.css';

		$this->variables->errors = [];

		$inn    = isset( $_GET['inn'] ) ? $_GET['inn'] : null;
		$status = isset( $_GET['status'] ) ? $_GET['status'] : null;

		$page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
		$limit  = self::ROWS_PER_PAGE;
		$offset = ( $page - 1 ) * $limit;

		$allowedStatuses = [
			StatementsSchema\Statuses::COMPLETE,
			StatementsSchema\Statuses::REJECTED
		];

		if ( ! preg_match( '/^((\d{10,10})|(\d{14,14}))$/', $inn ) ) {
			$inn = null;
		}

		if ( ! ( $status && in_array( $status, $allowedStatuses ) ) ) {
			$status = $allowedStatuses;
		}

		$this->variables->cInn    = $inn;
		$this->variables->cStatus = $status;

		$filters = [];

		if ( $inn ) {
			$filters['inn'] = $inn;
		}

		if ( $status ) {
			$filters['status-id'] = $status;
		}

		$dlStatuses   = new StatementsSchema\Statuses();
		$dlStatements = new StatementsSchema\Statements();

		try {
			$this->variables->statuses = $dlStatuses->getBy( [
				'status-id' => &$allowedStatuses
			] );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении состояний заявок.';
		}

		try {
			list( $count, $rows ) = $dlStatements->getBy(
				$filters,
				$limit,
				$offset
			);

			$this->context->paginator['count'] = (int) ceil( $count / $limit );

			$this->variables->count      = $count;
			$this->variables->statements = &$rows;
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка заявок.';
		}
	}
}

?>