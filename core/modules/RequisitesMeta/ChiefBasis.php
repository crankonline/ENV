<?php
namespace Environment\Modules\RequisitesMeta;

use Environment\DataLayers\Requisites\Meta\Activity;
use Environment\DataLayers\Requisites\Meta\ChiefBasis as DLChiefBasis;

class ChiefBasis extends \Environment\Core\Module {


    protected $config = [
        'template'   => 'layouts/RequisitesMeta/ChiefBasis.php',
        'listen'     => 'action',
        'skipMain' => false
    ];

    protected function view(int $id):void {
        $dlChiefBasis = new DLChiefBasis();
        $this->variables->data = $dlChiefBasis->getById($id);
        $this->config->template = 'layouts/RequisitesMeta/ChiefBasisEdit.php';
    }

    public function edit():void {

        if (0 === strlen(trim($_POST['name'] ?? ''))) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlChiefBasis = new DLChiefBasis();
            $dlChiefBasis->edit(
                $_POST['id'],
                $_POST['name']
            );
            $_POST = [];
            $this->variables->success = true;
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function addView():void {
        $this->config->template = 'layouts/RequisitesMeta/ChiefBasisAdd.php';
    }

    public function add():void {
        $this->addView();
        if (0 === strlen(trim($_POST['name'] ?? ''))) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlChiefBasis = new DLChiefBasis();
            $id = $dlChiefBasis->add($_POST['name']);
            $_POST = [];
            $this->variables->success = true;
            header("Location: index.php?view=meta-chief-basis&id=" . $id . '&success');
        }
        catch (\Exception $e) {
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

        $this->variables->chiefBasises = (new DLChiefBasis())->getChiefBasis();
    }

}