<?php

namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;

class Profile extends \Unikum\Core\Module {
	protected $config = [
		'template' => 'layouts/Profile/Default.html',
		'listen'   => 'action'
	];

	protected function validatePassword( array &$record ) {
		$e = [];

		$e['password'] = [];

		if ( empty( $record['password'] ) ) {
			$e['password'][] = 'Пароль не указан.';
		}

		$e['confirmation'] = [];

		if ( empty( $record['confirmation'] ) ) {
			$e['confirmation'][] = 'Пароль не подтвержден.';
		}

		if ( $record['password'] !== $record['confirmation'] ) {
			$e['password'][]     = 'Пароль и его подтверждение не совпадают.';
			$e['confirmation'][] = 'Пароль и его подтверждение не совпадают.';
		}

		return $e;
	}

	protected function canProceed( array &$result ) {
		foreach ( $result as &$section ) {
			if ( $section ) {
				return false;
			}
		}

		return true;
	}

	protected function readPostData( array $mapping ) {
		$record = [];

		foreach ( $mapping as $key ) {
			$record[ $key ] = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
		}

		return $record;
	}

	public function changePassword() {
		$mapping = [
			'password',
			'confirmation'
		];

		$user = &$_SESSION[ SESSION_USER_KEY ];

		$record = $this->readPostData( $mapping );
		$result = $this->validatePassword( $record );

		if ( $this->canProceed( $result ) ) {
			try {
				$dlUsers = new CoreSchema\Users();

				$dlUsers->changePassword( $user['id'], $record['password'] );

				$this->variables->result = true;
				$this->variables->status = 'Пароль учетной записи изменен.';

				$updated = $dlUsers->getById( $user['id'] );

				$user['is-password-expired'] = $updated['is-password-expired'];
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$this->variables->result = false;
				$this->variables->status = 'При изменении пароля учетной записи произошла ошибка.';
			}
		} else {
			$this->variables->result = false;
			$this->variables->status = 'Новый пароль и его подверждение введены некорректно. Проверьте сообщения у полей ввода.';

			$this->variables->validations = $result;
		}
	}

	public function changeModule() {
		$moduleId = null;

		if ( ! empty( $_POST['module-id'] ) ) {
			$moduleId = abs( (int) $_POST['module-id'] );
		}

		$user = &$_SESSION[ SESSION_USER_KEY ];

		$dlUsers = new CoreSchema\Users();

		try {
			$dlUsers->changeModule( $user['id'], $moduleId );

			$_SESSION[ SESSION_USER_KEY ] = $dlUsers->getById( $user['id'] );

			$this->variables->result = true;
			$this->variables->status = 'Стартовый модуль установлен.';
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->result = false;
			$this->variables->status = 'При назначении стартового модуля произошла ошибка.';
		}
	}

	protected function groupModules( array $records ) {
		foreach ( $records as $key => $record ) {
			$group = $record['module-group-name'] ?: 'Прочие';

			if ( ! isset( $records[ $group ] ) ) {
				$records[ $group ] = [];
			}

			$records[ $group ][] = $record;

			unset( $records[ $key ] );
		}

		return $records;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-profile.css';
		$this->context->css[] = 'resources/css/ui-misc-form.css';
		$this->context->css[] = 'resources/css/ui-misc-stripes.css';

		$this->context->title = 'Профиль';

		$this->variables->errors = [];

		$user = &$_SESSION[ SESSION_USER_KEY ];

		$this->variables->user = &$user;

		try {
			$dlVisits = new CoreSchema\Visits();

			$this->variables->visits = $dlVisits->getByUser( $user['id'], 10 );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении сведений о посещениях.';

			return;
		}

		try {
			$dlModules = new CoreSchema\Modules();

			$modules = $dlModules->getBy( [
				'user-role-id'   => $user['user-role-id'],
				'is-entry-point' => true
			] );

			if ( $modules ) {
				$moduleGroups = $this->groupModules( $modules );
			}

			$this->variables->moduleGroups = &$moduleGroups;
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении сведений о доступных модулях.';
		}
	}
}

?>