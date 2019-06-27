<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Environment\DataLayers\Environment\Core as CoreSchema;

class RegisterUserRole extends \Environment\Core\Module {
    protected $config = [
        'template'   => 'layouts/RegisterUserRole/Default.html',
        'listen'     => 'action'
    ];

    protected function validate(array &$record){
        $e = [];

        $e['name'] = [];

        if(empty($record['name'])){
            $e['name'][] = 'Наименование не указано.';
        }

        return $e;
    }

    protected function canRegister(array &$result){
        foreach($result as &$section){
            if($section){
                return false;
            }
        }

        return true;
    }

    protected function readPostData(array $mapping){
        $record  = [];

        foreach($mapping as $key){
            $record[$key] = isset($_POST[$key]) ? $_POST[$key] : null;
        }

        return $record;
    }

    public function register(){
        $mapping = [
            'name',
            'permissions'
        ];

        $record = $this->readPostData($mapping);
        $result = $this->validate($record);

        if($this->canRegister($result)){
            try {
                $dbms = Connections::getConnection('Environment');

                $dlUserRoles         = new CoreSchema\UserRoles($dbms);
                $dlModulePermissions = new CoreSchema\ModulePermissions($dbms);

                $dbms->beginTransaction();

                $id = $dlUserRoles->register($record);

                if($record['permissions']){
                    foreach($record['permissions'] as $permission){
                        $dlModulePermissions->allowToRole([
                            'user-role-id'  => $id,
                            'permission-id' => $permission
                        ]);
                    }
                }

                $dbms->commit();

                $this->variables->result = true;
                $this->variables->status = 'Роль учетных записей зарегистрирована.';

                $_POST = [];
            } catch(\PDOException $e) {
	            \Sentry\captureException($e);
                $dbms->rollBack();

                $this->variables->result = false;

                switch($e->getCode()){
                    case 23505:
                        $this->variables->status = 'Наименование уже используется для другой роли учетных записей.';
                    break;

                    default:
                        $this->variables->status = 'При регистрации роли учетных записей произошла ошибка.';
                    break;
                }
            }
        } else {
            $this->variables->result = false;
            $this->variables->status = 'Сведения роли учетных записей введены некорректно. Проверьте сообщения у полей ввода.';

            $this->variables->validations = $result;
        }
    }



    protected function groupPermissions(array $records){
        foreach($records as $key => $permission){
            $group = $permission['module-group-name'] ?: 'Прочие';

            if(!isset($records[$group])){
                $records[$group] = [];
            }

            $records[$group][] = $permission;

            unset($records[$key]);
        }

        return $records;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';

        $this->context->view = static::AK_USER_ROLES;

        $this->variables->errors = [];

        $dlModulePermissions = new CoreSchema\ModulePermissions();

        try {
            $permissions = $dlModulePermissions->getBy([]);

            if($permissions){
                $permissions = $this->groupPermissions($permissions);
            }

            $this->variables->permissions = &$permissions;
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении списка возможностей.';
        }
    }
}
?>