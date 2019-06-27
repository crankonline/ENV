<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Environment\DataLayers\Environment\Core as CoreSchema;

class UserRoleMembers extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/UserRoleMembers/Default.html',
        'listen'   => 'action'
    ];

    public function setRoleMembers(){
        $id    = isset($_GET['id']) ? abs((int)$_GET['id']) : null;
        $users = empty($_POST['users']) ? null : $_POST['users'];

        if(!($id && is_array($users) && $users)){
            return;
        }

        try {
            $dbms = Connections::getConnection('Environment');

            $dlUsers = new CoreSchema\Users($dbms);

            $dbms->beginTransaction();

            foreach($users as $user){
                $dlUsers->changeRole($user, $id);
            }

            $dbms->commit();

            $this->variables->result = true;
            $this->variables->status = 'Роль успешно назначена учетным записям.';
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $dbms->rollBack();

            $this->variables->result = false;
            $this->variables->status = 'При назначении роли произошла ошибка.';
        }
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';

        $this->context->view = static::AK_USER_ROLES;

        $this->variables->errors = [];

        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            $this->variables->errors[] = 'Роль учетных записей не задана.';
            return;
        }

        $dlUserRoles = new CoreSchema\UserRoles();
        $dlUsers     = new CoreSchema\Users();

        try {
            $this->variables->role = $dlUserRoles->getById($id);
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении сведений о роли учетных записей.';
            return;
        }

        if(!$this->variables->role){
            $this->variables->errors[] = 'Роль учетных записей не найдена.';
            return;
        }

        try {
            list(, $users)  = $dlUsers->getBy([
                'user-role-id-except' => $id
            ]);

            $this->variables->nonMemberUsers = $users;
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении сведений о доступных учетных записях.';
            return;
        }

        try {
            list(, $users)  = $dlUsers->getBy([
                'user-role-id' => $id
            ]);

            $this->variables->memberUsers = $users;
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении сведени об участвующих учетных записях.';
        }
    }
}
?>