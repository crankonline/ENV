<?php

namespace Environment\Modules\Plugins;

use Environment\DataLayers\Environment\Core as CoreSchema;

class Navigator extends \Unikum\Core\Module {
	const
		DEFAULT_ACCESS_KEY = 'profile',
		DEFAULT_MODULE = 'Profile';

	const
		PMS_ACCESS = 'can-access';

	protected $config = [
		'template' => 'layouts/Plugins/Navigator/Default.html'
	];

	protected function getModule( $userRoleId, $accessKey ) {
		$dlModules           = new CoreSchema\Modules();
		$dlModulePermissions = new CoreSchema\ModulePermissions();

		$modules = $dlModules->getBy( [
			'access-key' => $accessKey
		] );

		if ( ! $modules ) {
			throw new \Exception( 'Модуль не найден.' );
		}

		$module = &$modules[0];

		$permissions = $dlModulePermissions->getBy( [
			'user-role-id' => $userRoleId,
			'module-id'    => $module['id']
		] );

		foreach ( $permissions as &$permission ) {
			if ( $permission['mark'] === self::PMS_ACCESS ) {
				return $module;
			}
		}

		throw new \Exception( 'У Вас недостаточно привилегий для использования модуля.' );
	}

	protected function getPermissions( $userRoleId ) {
		$dlModulePermissions = new CoreSchema\ModulePermissions();

		$permissions = $dlModulePermissions->getBy( [
			'user-role-id' => $userRoleId
		] );

		$groups = [];

		foreach ( $permissions as $permission ) {
			$group = $permission['module-access-key'];
			$mark  = $permission['mark'];

			if ( ! isset( $groups[ $group ] ) ) {
				$groups[ $group ] = [];
			}

			$groups[ $group ][ $mark ] = true;
		}

		return $groups;
	}


	private function service( $service, $action ) {
		$className = 'Environment\\Services\\' . $service;
		$className::$action();
	}


	protected function main() {

		/*if(isset($_GET['service'])) {
			$this->service($_GET['service'], $_GET['action']);
			exit;
		}*/

		$user      = &$_SESSION[ SESSION_USER_KEY ];
		$accessKey = isset( $_GET['view'] ) ? $_GET['view'] : null;

		if ( ! $accessKey ) {
			$accessKey = $user['module-id']
				? $user['module-access-key']
				: self::DEFAULT_ACCESS_KEY;
		}

		if ( $accessKey == self::DEFAULT_ACCESS_KEY ) {
			$handler     = self::DEFAULT_MODULE;
			$module      = [];
			$permissions = [];
		} else {
			if ( $user['is-password-expired'] ) {
				$this->redirect( 'index.php?view=' . self::DEFAULT_ACCESS_KEY );
			} else {
				try {
					$module      = $this->getModule( $user['user-role-id'], $accessKey );
					$permissions = $this->getPermissions( $user['user-role-id'] );
					$handler     = $module['handler-class'];
				} catch ( \PDOException $e ) {
					\Sentry\captureException( $e );
					$error = 'В процессе навигации произошла ошибка СУБД.';
				} catch ( \Exception $e ) {
					\Sentry\captureException( $e );
					$error = $e->getMessage();
				}
			}
		}

		if ( isset( $error ) ) {
			$this->variables->error = &$error;
		} else {
			$this->suppress();

			if ( ! empty( $module ) ) {
				$this->context->title = $module['name'];
				$this->context->view  = $module['access-key'];
			}

			$config = [
				'context'     => $this->context,
				'module'      => $module,
				'permissions' => $permissions
			];

			$executable = 'Environment\\Modules\\' . $handler;

			$executable::run( $config );
		}
	}
}

?>