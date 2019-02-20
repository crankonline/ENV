<?php
namespace Environment\Modules\Plugins;

class Title extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/Title/Default.html'
    ];

    protected function main(){
        $title = isset($this->context->title) ? $this->context->title : null;

        if($title){
            $this->variables->title = $title;
        } else {
            $this->suppress();
        }
    }
}
?>