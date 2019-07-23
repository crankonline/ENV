<?php

namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;

class RegisterUser extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/RegisterUser/Default.html',
		'listen'   => 'action'
	];

	protected function validate( array &$record ) {
		$e = [];

		$reLogin = '/^[0-9A-Z\-\_\.]+$/i';

		$e['login'] = [];

		if ( empty( $record['login'] ) ) {
			$e['login'][] = 'Логин не указан.';
		} elseif ( ! preg_match( $reLogin, $record['login'] ) ) {
			$e['login'][] = 'Логин содержит недопустимые символы.';
		}

		if ( empty( $record['user-role-id'] ) ) {
			$e['user-role-id'][] = 'Роль не указана.';
		}

		$e['password'] = [];

		if ( empty( $record['password'] ) ) {
			$e['password'][] = 'Пароль не указан.';
		}

		$e['confirmation'] = [];

		if ( empty( $record['confirmation'] ) ) {
			$e['confirmation'][] = 'Пароль не подтвержден.';
		} elseif ( $record['password'] !== $record['confirmation'] ) {
			$e['password'][]     = 'Пароль и подтверждение пароля не совпадают.';
			$e['confirmation'][] = 'Пароль и подтверждение пароля не совпадают.';
		}

		$e['surname'] = [];

		if ( empty( $record['surname'] ) ) {
			$e['surname'][] = 'Фамилия не указана.';
		} elseif ( mb_strlen( $record['surname'], 'UTF-8' ) > 25 ) {
			$e['surname'][] = 'Длина фамилии превышает 25 символов.';
		}

		$e['name'] = [];

		if ( empty( $record['name'] ) ) {
			$e['name'][] = 'Имя не указано.';
		} elseif ( mb_strlen( $record['name'], 'UTF-8' ) > 20 ) {
			$e['name'][] = 'Длина имени превышает 20 символов.';
		}

		$e['middle-name'] = [];

		if ( empty( $record['middle-name'] ) ) {
			$record['middle-name'] = null;
		} elseif ( mb_strlen( $record['middle-name'], 'UTF-8' ) > 25 ) {
			$e['middle-name'][] = 'Длина отчества превышает 25 символов.';
		}

		$e['phone'] = [];

		if ( empty( $record['phone'] ) ) {
			$record['phone'] = null;
		} elseif ( strlen( $record['phone'] ) > 255 ) {
			$e['phone'][] = 'Длина телефона(-ов) превышает 255 символов.';
		}

		return $e;
	}

	protected function canRegister( array &$result ) {
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

	public function register() {
		$mapping = [
			'login',
			'user-role-id',
			'password',
			'confirmation',
			'surname',
			'name',
			'middle-name',
			'phone'
		];

		$record = $this->readPostData( $mapping );
		$result = $this->validate( $record );

		if ( $this->canRegister( $result ) ) {
			try {
				$dlUsers = new CoreSchema\Users();

				$id = $dlUsers->register( $record );

				$this->variables->result = true;
				$this->variables->status = 'Учетная запись зарегистрирована.';

				$_POST = [];
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$this->variables->result = false;

				switch ( $e->getCode() ) {
					case 23505:
						$this->variables->status = 'Логин уже используется для другой учетной записи.';
						break;

					default:
						$this->variables->status = 'При регистрации учетной записи произошла ошибка.';
						break;
				}
			}
		} else {
			$this->variables->result = false;
			$this->variables->status = 'Сведения учетной записи введены некорректно. Проверьте сообщения у полей ввода.';

			$this->variables->validations = $result;
		}
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';

		$this->context->view = static::AK_USERS;

		$this->variables->errors = [];

		$dlUserRoles = new CoreSchema\UserRoles();

		try {
			$this->variables->roles = $dlUserRoles->getAll();
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка ролей.';
		}
	}
}

?>