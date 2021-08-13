<?php
namespace Environment\Modules\RequisitesMeta;

use Environment\DataLayers\Requisites\Meta\Activity;
use Environment\DataLayers\Requisites\Meta\OwnershipForm;
use Environment\DataLayers\Requisites\Meta\LegalForm as DLLegalForm;

class Gked extends \Environment\Core\Module {

    protected $config = [
        'template'   => 'layouts/RequisitesMeta/Gked.php',
        'listen'     => 'action',
        'skipMain' => false
    ];

    protected function view(int $id):void {
        $dlActivity = new Activity();
        $this->variables->data = $dlActivity->getById($id);
        if($this->variables->data['ActivityID'] !== null) {
            $parent = $dlActivity->getById($this->variables->data['ActivityID']);
            $this->variables->data['ParentGked'] = $parent['Gked'];
        }
        else
        {
            $this->variables->data['ParentGked'] = '';
        }
        $this->config->template = 'layouts/RequisitesMeta/GkedEdit.php';
    }

    public function edit():void {

        if(
            in_array(
                0,
                [
                    strlen(trim($_POST['name'] ?? '')),
                    strlen(trim($_POST['gked'] ?? '')),
                    strlen(trim($_POST['parent'] ?? ''))
                ]
            )
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlActivity = new Activity();
            $dlActivity->edit(
                $_POST['id'],
                $_POST['gked'],
                $_POST['name'],
                $_POST['parent']
            );
            $_POST = [];
            $this->variables->success = true;
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function addView():void {
        $this->config->template = 'layouts/RequisitesMeta/GkedAdd.php';
    }


    public function add():void {
        $this->addView();
        if(
            in_array(
                0,
                [
                    strlen(trim($_POST['gked'] ?? '')),
                    strlen(trim($_POST['name'] ?? '')),
                    strlen(trim($_POST['parent'] ?? ''))
                ]
            )
        ) {
            $this->variables->errorMessage = 'Все поля должны быть заполнены';
            return;
        }

        try {
            $dlActivity = new Activity();
            $id = $dlActivity->add(
                $_POST['gked'],
                $_POST['name'],
                $_POST['parent']
            );
            $_POST = [];
            $this->variables->success = true;
            header("Location: index.php?view=meta-gked&id=" . $id . '&success');
        }
        catch (\Exception $e) {
            $this->variables->errorMessage = $e->getMessage();
        }
    }

    protected function main() {
        if(!empty($_GET['id'])) {
            $this->view($_GET['id']);
            return;
        }

        if(isset($_GET['add'])) {
            $this->addView();
            return;
        }

        $this->variables->gkeds = (new Activity())->getActivity();
    }


}