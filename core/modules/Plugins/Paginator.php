<?php
namespace Environment\Modules\Plugins;

class Paginator extends \Unikum\Core\Module {
    protected $config = [
        'template' => 'layouts/Plugins/Paginator/Default.html',

        'previousTpl' => '<a href="{url}">&lt;</a>',
        'nextTpl'     => '<a href="{url}">&gt;</a>',

        'defaultTpl' => '<a href="{url}">{page}</a>',
        'currentTpl' => '<a href="{url}" class="current">{page}</a>',

        'left'  => 2,
        'right' => 2,

        'marker' => 'page',

        'tplMarkers' => [ '{url}', '{page}' ]
    ];

    protected function getRenderedPage($tpl, array &$query, $marker, $page){
        $query[$marker] = $page;

        $src = &$this->config->tplMarkers;
        $dst = [ 'index.php?' . http_build_query($query), $page ];

        return str_replace($src, $dst, $tpl);
    }

    protected function getBounds($current, $count, $left, $right){
        if($current > $count){
            $current = $count;
        }

        if(($current > $left) && ($current < ($count - $right))){
            $from = $current - $left;
            $to   = $current + $right;
        } elseif($current <= $left) {
            $slice = 1 + $left - $current;
            $from  = 1;
            $to    = min($current + ($right + $slice), $count);
        } else {
            $slice = $right - ($count - $current);
            $from  = max($current - ($left + $slice), 1);
            $to    = $count;
        }

        return [ $from, $to ];
    }

    protected function main(){
        $ctx = &$this->context->paginator;

        $count = $ctx['count'];

        if($count < 2){
            return $this->suppress();
        }

        $this->context->css[] = 'resources/css/ui-paginator.css';

        $defaultTpl = isset($ctx['default-template'])
            ? $ctx['default-template']
            : $this->config->defaultTpl;

        $currentTpl = isset($ctx['current-template'])
            ? $ctx['current-template']
            : $this->config->currentTpl;

        $previousTpl = isset($ctx['previous-template'])
            ? $ctx['previous-template']
            : $this->config->previousTpl;

        $nextTpl = isset($ctx['next-template'])
            ? $ctx['next-template']
            : $this->config->nextTpl;

        $left  = isset($ctx['left']) ? $ctx['left'] : $this->config->left;
        $right = isset($ctx['right']) ? $ctx['right'] : $this->config->right;

        $marker  = isset($ctx['marker']) ? $ctx['marker'] : $this->config->marker;
        $current = isset($_GET[$marker]) ? (abs((int)$_GET[$marker]) ?: 1) : 1;

        list($from, $to) = $this->getBounds($current, $count, $left, $right);

        $this->variables->defaultTpl  = $defaultTpl;
        $this->variables->currentTpl  = $currentTpl;
        $this->variables->previousTpl = $previousTpl;
        $this->variables->nextTpl     = $nextTpl;

        $this->variables->marker  = $marker;
        $this->variables->current = $current;
        $this->variables->count   = $count;
        $this->variables->from    = $from;
        $this->variables->to      = $to;
        $this->variables->query   = $_GET;
    }
}
?>