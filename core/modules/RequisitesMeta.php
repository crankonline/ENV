<?php
namespace Environment\Modules;

use Environment\DataLayers\Requisites\Meta\LegalForm;
use Environment\DataLayers\Requisites\Meta\Bank;
use Environment\DataLayers\Requisites\Meta\Activity;
use Environment\DataLayers\Requisites\Meta\ChiefBasis;

class RequisitesMeta extends \Environment\Core\Module {
	protected $config = [
		'template'   => 'layouts/RequisitesMeta/Default.php',
		'listen'     => 'action',
		'skipMain' => false
	];

	public function getLegalFormJson() {
		$dlLegalForm = new LegalForm();
		$return = $dlLegalForm->getLegalForm();
		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode($return) ); //чтоб не подгружался вышестоящий темплейт.
	}

	public function editLegalForm() {
		$dlLegalForm = new LegalForm();
		if(isset($_POST['id']) &&
		   isset($_POST['name']) &&
		   isset($_POST['short'])) {
			$id = $_POST['id'];
			$name = $_POST['name'];
			$shortName = $_POST['short'];
		}

		$return = $dlLegalForm->modifyLegalForm($id, $name, $shortName);

		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode(["id"=>$id,
		                  "name"=>$name,
		                  "shortName"=>$shortName,
		                  "return"=>$return]) ); //чтоб не подгружался вышестоящий темплейт.

	}

	public function getBankJson() {
		$dlBank = new Bank();
		$return = $dlBank->getBank("");
		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode($return) ); //чтоб не подгружался вышестоящий темплейт.
	}

	public function editBank() {
		$dlBank = new Bank();
		if(isset($_POST['bankId']) &&
		   isset($_POST['bankName']) &&
		   isset($_POST['bankAddress'])) {
			$id = $_POST['bankId'];
			$name = $_POST['bankName'];
			$shortName = $_POST['bankAddress'];
		}

		$return = $dlBank->modifyBank($id, $name, $shortName);

		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode(["id"=>$id,
		                  "name"=>$name,
		                  "shortName"=>$shortName,
		                  "return"=>$return]) ); //чтоб не подгружался вышестоящий темплейт.

	}

	public function getActivityJson() {
		$dlActivity = new Activity();
		$return = $dlActivity->getActivity();
		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode($return) ); //чтоб не подгружался вышестоящий темплейт.
	}

	public function editActivity() {
		$dlActivity = new Activity();
		if(isset($_POST['id']) &&
		   isset($_POST['activityId']) &&
		   isset($_POST['activityName']) &&
		   isset($_POST['activityGked'])) {
			$id = $_POST['id'];
			$activityId = $_POST['activityId'];
			$activityName = $_POST['activityName'];
			$activityGked = $_POST['activityGked'];

			$return = $dlActivity->modifyActivity($id, $activityId, $activityName, $activityGked);
		}




		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode(["id"=>$id,
		                  "activityId"=>$activityId,
		                  "activityName"=>$activityName,
		                  "activityGked"=>$activityGked,
		                  "return"=>$return] ) ); //чтоб не подгружался вышестоящий темплейт.
	}

	public function getChiefBasisJson(){
		$dlChiefBasis = new ChiefBasis();
		$return = $dlChiefBasis->getChiefBasis();
		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode($return) ); //чтоб не подгружался вышестоящий темплейт.
	}

	public function editChiefBasis() {
		 $dlChiefBasis = new ChiefBasis();
		if(isset($_POST['id']) &&
		   isset($_POST['name'])) {
			$id = $_POST['id'];
			$name = $_POST['name'];

			$return = $dlChiefBasis->modifyChiefBasis($id, $name);
		}

		$this->config->skipMain = true;

		$this->suppress();

		die( json_encode(["id"=>$id,
		                  "name"=>$name,
		                  "return"=>$return] ) ); //чтоб не подгружался вышестоящий темплейт.
	}

	protected function main() {
		if($this->config->skipMain){
			return;
		}
		$this->context->css[] = "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css";
//		$this->variables->data = [
//			'legalForm' => self::getLegalForm()
//		];
//
//		$this->variables->legalForm = self::getLegalForm();
	}



}