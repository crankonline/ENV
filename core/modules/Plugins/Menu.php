<?php
namespace Environment\Modules\Plugins;

use Environment\DataLayers\Environment\Core as CoreSchema;

class Menu extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/Menu/Default.html'
    ];

    protected function main(){
        $user = &$_SESSION[SESSION_USER_KEY];

        if($this->context->view){
            $currentAccessKey = $this->context->view;
        } elseif(isset($_GET['view'])) {
            $currentAccessKey = $_GET['view'];
        } else {
            $currentAccessKey = Navigator::DEFAULT_ACCESS_KEY;
        }

        $this->variables->currentAccessKey = $currentAccessKey;

        try {
            $dlModules = new CoreSchema\Modules();

            $modules = $dlModules->getBy([
                'user-role-id'   => $user['user-role-id'],
                'is-entry-point' => true
            ]);

            $groups = [];

            foreach($modules as $module){
                $group = $module['module-group-id']
                    ? $module['module-group-name']
                    : 'Прочие';

                if(!isset($groups[$group])){
                    $groups[$group] = [];
                }

                $groups[$group][] = $module;
            }
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->error = 'При формировании меню произошла ошибка СУБД';
        }

        $this->variables->groups = &$groups;
    }
}
?>