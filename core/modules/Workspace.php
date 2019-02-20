<?php
namespace Environment\Modules;

use Environment\Modules\Plugins as Plugins;

class Workspace extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Workspace/Default.html',
        'plugins'  => [
            'navigator' => Plugins\Navigator::class,
            'title'     => Plugins\Title::class,
            'menu'      => Plugins\Menu::class,
            'js'        => Plugins\Js::class,
            'css'       => Plugins\Css::class
        ]
    ];

    protected function main(){
        $this->context->js  = [];
        $this->context->css = [];

        $this->context->css[] = 'resources/css/ui.css';
        $this->context->css[] = 'resources/css/ui-misc-messages.css';

        $this->context->view = null;

        $this->variables->user = &$_SESSION[SESSION_USER_KEY];
    }
}
?>
