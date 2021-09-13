<?php

namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;
use PDOException;
use Unikum\Core\Module;
use function Sentry\captureException;

class Profile extends Module {
	/**
	 * @var string[]
	 */
	protected $config = [
		'template' => 'layouts/Profile/Default.html',
		'listen'   => 'action'
	];

	/**
	 * @param array $record
	 * @return array
	 */
	protected function validatePassword(array &$record ): array {
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

	/**
	 * @param array $result
	 * @return bool
	 */
	protected function canProceed(array &$result ): bool {
		foreach ( $result as &$section ) {
			if ( $section ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $mapping
	 * @return array
	 */
	protected function readPostData(array $mapping ): array {
		$record = [];

		foreach ( $mapping as $key ) {
			$record[ $key ] = $_POST[$key] ?? null;
		}

		return $record;
	}

	/**
	 *
	 */
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
			} catch ( PDOException $e ) {
				captureException( $e );
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
		} catch ( PDOException $e ) {
			captureException( $e );
			$this->variables->result = false;
			$this->variables->status = 'При назначении стартового модуля произошла ошибка.';
		}
	}

	/**
	 * @param array $records
	 * @return array
	 */
	protected function groupModules(array $records ): array {
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

	/**
	 *
	 */
	public function keep() {
	    exit;
    }

	/**
	 * @return void|null
	 */
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
		} catch ( PDOException $e ) {
			captureException( $e );
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
		} catch ( PDOException $e ) {
			captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении сведений о доступных модулях.';
		}
	}
}
