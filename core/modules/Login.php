<?php
namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;

class Login extends \Unikum\Core\Module {
	protected $config = [
		'template' => 'layouts/Login/Default.html',
		'plugins'  => [
			'css' => Plugins\Css::class
		]
	];

	protected function authenticate( $login, $password ) {
		session_name( SESSION_INITIAL_NAME );
		session_start();
		session_regenerate_id();

		$dlUsers = new CoreSchema\Users();

		$user = $dlUsers->authenticate( $login, $password );

		if ( $user ) {
			if ( ! $user['is-active'] ) {
				throw new \Exception( 'Учетная запись заблокирована. Обратитесь к администратору.' );
			}

			$dlVisits = new CoreSchema\Visits();

			$dlVisits->register( [
				'user-id'    => $user['id'],
				'ip-address' => $_SERVER['REMOTE_ADDR']
			] );

			$_SESSION[ SESSION_USER_KEY ] = $user;
		}

		return (bool) $user;
	}

	protected function main() {
		$this->context->css = [ 'resources/css/login.css' ];

		if ( $_POST ) {
			try {
				if ( ! isset( $_POST['login'], $_POST['password'] ) ) {
					throw new \Exception( 'Не указан логин или пароль' );
				}

				if ( $this->authenticate( $_POST['login'], $_POST['password'] ) ) {
					$this->redirect( SYSTEM_HOST );
				} else {
					throw new \Exception( 'Неверный логин или пароль' );
				}
			} catch ( \PDOException $e ) {
				\Sentry\captureException( $e );
				$this->variables->message = 'Произошла ошибка при работе с БД';
			} catch ( \Exception $e ) {
//				\Sentry\captureException( $e );
				$this->variables->message = $e->getMessage();
			}
		}
	}
}

?>
