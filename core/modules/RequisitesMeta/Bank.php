<?php
namespace Environment\Modules\RequisitesMeta;

use Environment\DataLayers\Requisites\Meta\Bank as DLBank;
use Environment\DataLayers\Requisites\Meta\OwnershipForm;
use Environment\DataLayers\Requisites\Meta\LegalForm as DLLegalForm;

class Bank extends \Environment\Core\Module {

    protected $config = [
        'template'   => 'layouts/RequisitesMeta/Bank.php',
        'listen'     => 'action',
        'skipMain' => false
    ];

    protected function view(int $id):void {
        $this->variables->data = (new DLBank())->getBank($id);
        $this->config->template = 'layouts/RequisitesMeta/BankEdit.php';
    }

    protected function addView():void {
        $this->config->template = 'layouts/RequisitesMeta/BankAdd.php';
    }

    public function add():void {
        $this->addView();
        if(
            in_array(
                0,
                [
                    strlen(trim($_POST['name'] ?? '')),
                    strlen(trim($_POST['bik'] ?? '')),
                    strlen(trim($_POST['address'] ?? ''))
                ]
            )
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlBank = new DLBank();
            $id = $dlBank->add(
                $_POST['bik'],
                $_POST['name'],
                $_POST['address']
            );
            $_POST = [];
            $this->variables->success = true;
            header("Location: index.php?view=meta-bank&id=" . $id . '&success');
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    public function edit():void {

        if(
            in_array(
                0,
                [
                    strlen(trim($_POST['name'] ?? '')),
                    strlen(trim($_POST['bik'] ?? '')),
                    strlen(trim($_POST['address'] ?? ''))
                ]
            )
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlBank = new DLBank();
            $dlBank->edit(
                $_POST['bik'],
                $_POST['name'],
                $_POST['address'],
                $_POST['oldbik']
            );
            header("Location: index.php?view=meta-bank&id={$_POST['bik']}&success");
            $_POST = [];
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

        $this->variables->banks = (new DLBank())->getBank();
    }


}