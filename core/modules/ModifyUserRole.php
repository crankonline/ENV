<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
	Environment\DataLayers\Environment\Core as CoreSchema;

class ModifyUserRole extends \Environment\Core\Module {
	protected $config = [
		'template' => 'layouts/ModifyUserRole/Default.html',
		'listen'   => 'action'
	];

	protected function validate( array &$record ) {
		$e = [];

		$e['name'] = [];

		if ( empty( $record['name'] ) ) {
			$e['name'][] = 'Наименование не указано.';
		}

		return $e;
	}

	protected function canModify( array &$result ) {
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

	public function modify() {
		$id = isset( $_GET['id'] ) ? abs( (int) $_GET['id'] ) : null;

		if ( ! $id ) {
			return;
		}

		$mapping = [
			'name',
			'permissions'
		];

		$record = $this->readPostData( $mapping );
		$result = $this->validate( $record );

		if ( $this->canModify( $result ) ) {
			try {
				$dbms = Connections::getConnection( 'Environment' );

				$dlUserRoles         = new CoreSchema\UserRoles( $dbms );
				$dlModulePermissions = new CoreSchema\ModulePermissions( $dbms );

				$dbms->beginTransaction();

				$dlUserRoles->modify( $id, $record );

				$dlModulePermissions->forbidAllToRole( $id );

				if ( $record['permissions'] ) {
					foreach ( $record['permissions'] as $permission ) {
						$dlModulePermissions->allowToRole( [
							'user-role-id'  => $id,
							'permission-id' => $permission
						] );
					}
				}

				$dbms->commit();

				$this->variables->result = true;
				$this->variables->status = 'Роль учетных записей изменена.';

				$_POST = [];
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$dbms->rollBack();

				$this->variables->result = false;

				switch ( $e->getCode() ) {
					case 23505:
						$this->variables->status = 'Наименование уже используется для другой роли учетных записей.';
						break;

					default:
						$this->variables->status = 'При изменении роли учетных записей произошла ошибка.';
						break;
				}
			}
		} else {
			$this->variables->result = false;
			$this->variables->status = 'Сведения роли учетных записей введены некорректно. Проверьте сообщения у полей ввода.';

			$this->variables->validations = $result;
		}
	}

	public function remove() {
		$id = isset( $_GET['id'] ) ? abs( (int) $_GET['id'] ) : null;

		if ( ! $id ) {
			return;
		}

		try {
			$dlUserRoles = new CoreSchema\UserRoles();

			$dlUserRoles->remove( $id );

			if ( $this->isPermitted( self::AK_USER_ROLES ) ) {
				$this->redirect( 'index.php?view=' . self::AK_USER_ROLES );
			}
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->result = false;

			switch ( $e->getCode() ) {
				case 23503:
					$this->variables->status = 'Роль используется учетными записями и не может быть удалена.';
					break;

				default:
					$this->variables->status = 'При удалении роли учетных записей произошла ошибка.';
					break;
			}
		}
	}

	protected function groupPermissions( array $records ) {
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

	protected function extractIds( array $records ) {
		$result = [];

		foreach ( $records as $record ) {
			$result[] = $record['id'];
		}

		return $result;
	}

	protected function main() {
		$this->context->css[] = 'resources/css/ui-misc-form.css';

		$this->context->view = static::AK_USER_ROLES;

		$this->variables->errors = [];

		$id = isset( $_GET['id'] ) ? abs( (int) $_GET['id'] ) : null;

		if ( ! $id ) {
			$this->variables->errors[] = 'Роль учетных записей не задана.';

			return;
		}

		$dlUserRoles         = new CoreSchema\UserRoles();
		$dlModulePermissions = new CoreSchema\ModulePermissions();

		try {
			$this->variables->role = $dlUserRoles->getById( $id );
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении сведений о роли учетных записей.';

			return;
		}

		if ( ! $this->variables->role ) {
			$this->variables->errors[] = 'Роль учетных записей не найдена.';

			return;
		}

		try {
			$permissions = $dlModulePermissions->getBy( [] );

			if ( $permissions ) {
				$permissions = $this->groupPermissions( $permissions );
			}

			$this->variables->permissions = &$permissions;
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка возможностей.';

			return;
		}

		try {
			$this->variables->role['permissions'] = $dlModulePermissions->getBy( [
				'user-role-id' => $id
			] );

			if ( $this->variables->role['permissions'] ) {
				$this->variables->role['permissions'] = $this->extractIds(
					$this->variables->role['permissions']
				);
			}
		} catch ( \PDOException $e ) {
			\Sentry\captureException( $e );
			$this->variables->errors[] = 'Произошла ошибка при получении списка возможностей роли учетных записей.';
		}
	}
}

?>