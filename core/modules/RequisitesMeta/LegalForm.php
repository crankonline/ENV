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
        $_POST['ownershipform'] = $_POST['ownershipform'] ?? 0;
        $this->addView();
        if(
            in_array(
                0,
                [
                    strlen(trim($_POST['name'] ?? '')),
                    strlen(trim($_POST['shortname'] ?? '')),
                    strlen(trim($_POST['facet'] ?? '')),
                    strlen(trim($_POST['ownershipform'] ?? ''))
                ]
            )
            ||
            (string)($_POST['ownershipform'] ?? '') === '0'
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        if(!is_numeric($_POST['facet'])) {
            $this->variables->errorMessage = 'Фасет должен быть целочисленным';
            return;
        }

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
            header("Location: index.php?view=meta-legal-form&id=" . $id . '&success');
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
                    strlen(trim($_POST['shortname'] ?? '')),
                    strlen(trim($_POST['facet'] ?? '')),
                    strlen(trim($_POST['ownershipform'] ?? ''))
                ]
            )
            ||
            (string)($_POST['ownershipform'] ?? '') === '0'
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        if(!is_numeric($_POST['facet'])) {
            $this->variables->errorMessage = 'Фасет должен быть целочисленным';
            return;
        }

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