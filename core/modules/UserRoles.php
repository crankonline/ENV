<?php
namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;

class UserRoles extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/UserRoles/Default.html'
    ];

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-user-roles.css';

        $dlUserRoles = new CoreSchema\UserRoles();

        try {
            $this->variables->roles = $dlUserRoles->getAll();
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->error = 'Произошла ошибка при получении списка ролей.';
        }
    }
}
?>