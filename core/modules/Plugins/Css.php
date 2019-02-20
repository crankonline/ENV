<?php
namespace Environment\Modules\Plugins;

class Css extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/Css/Default.html'
    ];

    protected function main(){
        $this->variables->stylesheets = &$this->context->css;
    }
}
?>