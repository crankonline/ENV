<?php
namespace Environment\Modules\RequisitesMeta;

use Environment\DataLayers\Requisites\Meta\OwnershipForm;
use Environment\DataLayers\Requisites\Meta\LegalForm as DLLegalForm;

class LegalForm extends \Environment\Core\Module {

    protected $config = [
        'template'   => 'layouts/RequisitesMeta/LegalForm.php',
        'listen'     => 'action',
        'skipMain' => false
    ];

    protected function view(int $id):void {
        $this->variables->data = (new DLLegalForm())->getById($id);
        $this->config->template = 'layouts/RequisitesMeta/LegalFormEdit.php';
    }

    protected function addView():void {
        $this->config->template = 'layouts/RequisitesMeta/LegalFormAdd.php';
    }

    public function add():void {
        try {
            $dlLegalForm = new DLLegalForm();
            $id = $dlLegalForm->add(
                $_POST['name'],
                $_POST['shortname'],
                (int)$_POST['facet'],
                (int)$_POST['ownershipform']
            );
            $_POST = [];
            $this->variables->success = true;
            header("Location: index.php?view=meta-legal-form&id=" . $id);
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    public function edit():void {
        try {
            $dlLegalForm = new DLLegalForm();
            $dlLegalForm->edit(
                $_POST['id'],
                $_POST['name'],
                $_POST['shortname'],
                (int)$_POST['facet'],
                (int)$_POST['ownershipform']
            );
            $_POST = [];
            $this->variables->success = true;
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function main() {
        $ownershipForms = (new OwnershipForm())->getOwnershipForms();
        $this->variables->ownershipForms = [];
        foreach ($ownershipForms as $ownershipForm) {
            $this->variables->ownershipForms[$ownershipForm['IDOwnershipForm']] = $ownershipForm['Name'];
        }

        if(isset($_GET['add'])) {
            $this->addView();
            return;
        }

        if(!empty($_GET['id'])) {
            $this->view($_GET['id']);
            return;
        }

        $this->variables->legalForms = (new DLLegalForm())->getLegalForm();
    }


}