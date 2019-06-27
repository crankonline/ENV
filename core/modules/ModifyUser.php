<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Environment\DataLayers\Environment\Core as CoreSchema;

class ModifyUser extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/ModifyUser/Default.html',
        'listen'   => 'action'
    ];

    protected function validateModify(array &$record){
        $e = [];

        $reLogin = '/^[0-9A-Z\-\_\.]+$/i';

        $e['login'] = [];

        if(empty($record['login'])){
            $e['login'][] = 'Логин не указан.';
        } elseif(!preg_match($reLogin, $record['login'])){
            $e['login'][] = 'Логин содержит недопустимые символы.';
        }

        if(empty($record['user-role-id'])){
            $e['user-role-id'][] = 'Роль не указана.';
        }

        $e['surname'] = [];

        if(empty($record['surname'])){
            $e['surname'][] = 'Фамилия не указана.';
        } elseif(mb_strlen($record['surname'], 'UTF-8') > 25){
            $e['surname'][] = 'Длина фамилии превышает 25 символов.';
        }

        $e['name'] = [];

        if(empty($record['name'])){
            $e['name'][] = 'Имя не указано.';
        } elseif(mb_strlen($record['name'], 'UTF-8') > 20){
            $e['name'][] = 'Длина имени превышает 20 символов.';
        }

        $e['middle-name'] = [];

        if(empty($record['middle-name'])){
            $record['middle-name'] = null;
        } elseif(mb_strlen($record['middle-name'], 'UTF-8') > 25) {
            $e['middle-name'][] = 'Длина отчества превышает 25 символов.';
        }

        $e['phone'] = [];

        if(empty($record['phone'])) {
            $record['phone'] = null;
        } elseif(strlen($record['phone']) > 255) {
            $e['phone'][] = 'Длина телефона(-ов) превышает 255 символов.';
        }

        return $e;
    }

    protected function validatePassword(array &$record){
        $e = [];

        $e['password'] = [];

        if(empty($record['password'])){
            $e['password'][] = 'Пароль не указан.';
        }

        $e['confirmation'] = [];

        if(empty($record['confirmation'])){
            $e['confirmation'][] = 'Пароль не подтвержден.';
        }

        if($record['password'] !== $record['confirmation']){
            $e['password'][]     = 'Пароль и его подтверждение не совпадают.';
            $e['confirmation'][] = 'Пароль и его подтверждение не совпадают.';
        }

        return $e;
    }

    protected function canProceed(array &$result){
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

    public function modify(){
        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            return;
        }

        $mapping = [
            'login',
            'user-role-id',
            'surname',
            'name',
            'middle-name',
            'phone'
        ];

        $record = $this->readPostData($mapping);
        $result = $this->validateModify($record);

        if($this->canProceed($result)){
            try {
                $dlUsers = new CoreSchema\Users();

                $dlUsers->modify($id, $record);

                $this->variables->result = true;
                $this->variables->status = 'Сведения учетной записи обновлены.';
            } catch(\PDOException $e) {
	            \Sentry\captureException($e);
                $this->variables->result = false;

                switch($e->getCode()){
                    case 23505:
                        $this->variables->status = 'Логин уже используется для другой учетной записи.';
                    break;

                    default:
                        $this->variables->status = 'При обновлении сведений учетной записи произошла ошибка.';
                    break;
                }
            }
        } else {
            $this->variables->result = false;
            $this->variables->status = 'Сведения учетной записи введены некорректно. Проверьте сообщения у полей ввода.';

            $this->variables->validations = $result;
        }
    }

    public function setActive(){
        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            return;
        }

        try {
            $dlUsers = new CoreSchema\Users();

            $user = $dlUsers->getById($id);

            if(!$user){
                return;
            }

            $state = (int)$user['is-active'] ? 0 : 1;

            $dlUsers->setActivity($id, $state);

            $this->variables->result = true;
            $this->variables->status = 'Учетная запись ' . ($state ? 'активирована' : 'заблокирована') . '.';
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->result = false;
            $this->variables->status = 'При обновлении сведений учетной записи произошла ошибка.';
        }
    }

    public function changePassword(){
        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            return;
        }

        $mapping = [
            'password',
            'confirmation'
        ];

        $record = $this->readPostData($mapping);
        $result = $this->validatePassword($record);

        if($this->canProceed($result)){
            try {
                $dlUsers = new CoreSchema\Users();

                $dlUsers->changePassword($id, $record['password']);

                $this->variables->result = true;
                $this->variables->status = 'Пароль учетной записи изменен.';
            } catch(\PDOException $e) {
	            \Sentry\captureException($e);
                $this->variables->result = false;
                $this->variables->status = 'При изменении пароля учетной записи произошла ошибка.';
            }
        } else {
            $this->variables->result = false;
            $this->variables->status = 'Новый пароль и его подверждение введены некорректно. Проверьте сообщения у полей ввода.';

            $this->variables->validations = $result;
        }
    }

    public function setPasswordExpired(){
        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            return;
        }

        try {
            $dlUsers = new CoreSchema\Users();

            $user = $dlUsers->getById($id);

            if(!$user){
                return;
            }

            $state = (int)$user['is-password-expired'] ? 0 : 1;

            $dlUsers->setPasswordExpired($id, $state);

            $this->variables->result = true;
            $this->variables->status = 'Пароль помечен как "' . ($state ? 'устаревший' : 'актуальный') . '".';
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->result = false;
            $this->variables->status = 'При обновлении сведений учетной записи произошла ошибка.';
        }
    }

    public function remove(){
        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            return;
        }

        try {
            $dlUsers = new CoreSchema\Users();

            $dlUsers->remove($id);

            if($this->isPermitted(self::AK_USERS)){
                $this->redirect('index.php?view=' . self::AK_USERS);
            }
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->result = false;
            $this->variables->status = 'При удалении учетной записи произошла ошибка.';
        }
    }

    public function changeModule(){
        $id       = isset($_GET['id']) ? abs((int)$_GET['id']) : null;
        $moduleId = empty($_POST['module-id']) ? null : abs((int)$_POST['module-id']);

        if(!$id){
            return;
        }

        $dlUsers = new CoreSchema\Users();

        try {
            $user = $dlUsers->getById($id);

            if(!$user){
                return;
            }

            $dlUsers->changeModule($user['id'], $moduleId);

            if($user['id'] === $_SESSION[SESSION_USER_KEY]['id']){
                $_SESSION[SESSION_USER_KEY] = $dlUsers->getById($user['id']);
            }

            $this->variables->result = true;
            $this->variables->status = 'Стартовый модуль учетной записи установлен.';
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->result = false;
            $this->variables->status = 'При назначении стартового модуля учетной записи произошла ошибка.';
        }
    }

    protected function groupModules(array $records){
        foreach($records as $key => $record){
            $group = $record['module-group-name'] ?: 'Прочие';

            if(!isset($records[$group])){
                $records[$group] = [];
            }

            $records[$group][] = $record;

            unset($records[$key]);
        }

        return $records;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';

        $this->context->view = static::AK_USERS;

        $this->variables->errors = [];

        $id = isset($_GET['id']) ? abs((int)$_GET['id']) : null;

        if(!$id){
            $this->variables->errors[] = 'Учетная запись не задана.';
            return;
        }

        $dlUsers     = new CoreSchema\Users();
        $dlUserRoles = new CoreSchema\UserRoles();
        $dlModules   = new CoreSchema\Modules();

        try {
            $this->variables->user = $dlUsers->getById($id);
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении сведений учетной записи.';
            return;
        }

        if(!$this->variables->user){
            $this->variables->errors[] = 'Учетная запись не найдена.';
            return;
        }

        try {
            $this->variables->roles = $dlUserRoles->getAll();
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении списка ролей.';
        }

        try {
            $modules = $dlModules->getBy([
                'user-role-id'   => $this->variables->user['user-role-id'],
                'is-entry-point' => true
            ]);

            if($modules){
                $moduleGroups = $this->groupModules($modules);
            }

            $this->variables->moduleGroups = &$moduleGroups;
        } catch(\PDOException $e) {
	        \Sentry\captureException($e);
            $this->variables->errors[] = 'Произошла ошибка при получении сведений о доступных модулях.';
        }
    }
}
?>