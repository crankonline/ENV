<?php
namespace Environment\Modules\RequisitesMeta;

use Environment\Core\Module;
use Environment\DataLayers\Requisites\Meta\Position as DLPosition;
use Exception;

class Position extends Module {


    protected $config = [
        'template'   => 'layouts/RequisitesMeta/Position.php',
        'listen'     => 'action',
        'skipMain' => false
    ];

    protected function view(int $id):void {
        $dlPosition = new DLPosition();
        $this->variables->data = $dlPosition->getById($id);
        $this->config->template = 'layouts/RequisitesMeta/PositionEdit.php';
    }

    public function edit():void {

        if (0 === strlen(trim($_POST['name'] ?? ''))) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlPosition = new DLPosition();
            $dlPosition->edit(
                $_POST['id'],
                $_POST['name']
            );
            $_POST = [];
            $this->variables->success = true;
        }
        catch (Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function addView():void {
        $this->config->template = 'layouts/RequisitesMeta/PositionAdd.php';
    }

    public function add():void {
        $this->addView();
        if (0 === strlen(trim($_POST['name'] ?? ''))) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlPosition = new DLPosition();
            $id = $dlPosition->add($_POST['name']);
            $_POST = [];
            $this->variables->success = true;
            header("Location: index.php?view=meta-position&id=" . $id . '&success');
        }
        catch (Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function main() {
        $this->context->css[] = 'resources/css/ui-clients-list.css';
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-messages.css';

        if(!empty($_GET['id'])) {
            $this->view($_GET['id']);
            return;
        }

        if(isset($_GET['add'])) {
            $this->addView();
            return;
        }

        $this->variables->positions = (new DLPosition())->getPositions();
    }

}
