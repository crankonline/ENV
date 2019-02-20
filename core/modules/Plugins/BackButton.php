<?php
namespace Environment\Modules\Plugins;

class BackButton extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/BackButton/Default.html'
    ];

    protected function main(){
        $enable  = $this->context->back['enable'];
        $listen  = $this->context->back['listen'];
        $default = $this->context->back['default'];

        if(!$enable){
            return $this->suppress();
        }

        $this->variables->url = isset($_GET[$listen]) ? $_GET[$listen] : $default;
    }
}
?>