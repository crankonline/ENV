<?php
namespace Environment\Modules\Plugins;

class Js extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/Js/Default.html'
    ];

    protected function main(){
        $this->variables->scripts = &$this->context->js;
    }
}
?>