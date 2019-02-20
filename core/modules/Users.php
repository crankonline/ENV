<?php
namespace Environment\Modules;

use Environment\DataLayers\Environment\Core as CoreSchema;

class Users extends \Environment\Core\Module {
    const ROWS_PER_PAGE = 30;

    protected $config = [
        'template' => 'layouts/Users/Default.html',
        'plugins'  => [
            'paginator' => Plugins\Paginator::class
        ]
    ];

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-users.css';

        $page   = isset($_GET['page']) ? (abs((int)$_GET['page']) ?: 1) : 1;
        $limit  = self::ROWS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $dlUsers = new CoreSchema\Users();

        try {
            list($count, $users) = $dlUsers->getBy([], $limit, $offset);

            $this->context->paginator['count'] = (int)ceil($count / $limit);

            $this->variables->count = $count;
            $this->variables->users = &$users;
        } catch(\PDOException $e) {
            $this->variables->error = 'Произошла ошибка при получении списка учетных записей.';
        }
    }
}
?>
