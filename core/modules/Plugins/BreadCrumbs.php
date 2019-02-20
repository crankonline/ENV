<?php
namespace Environment\Modules\Plugins;

class BreadCrumbs extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/BreadCrumbs/Default.html'
    ];

    protected function main(){
        $this->variables->crumbs = &$this->context->crumbs;
        $this->variables->count  = count($this->variables->crumbs) - 1;

        foreach($this->variables->crumbs as &$crumb){
            if(empty($crumb['href'])){
                $crumb['href'] = '#';
            }

            if(empty($crumb['text'])){
                $crumb['text'] = '?';
            }

            if(empty($crumb['type'])){
                $crumb['type'] = 'normal';
            }
        }
    }
}
?>