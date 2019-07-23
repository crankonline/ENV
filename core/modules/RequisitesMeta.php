<?php
namespace Environment\Modules;

use Environment\DataLayers\Requisites\Meta\LegalForm;

class RequisitesMeta extends \Environment\Core\Module {
	protected $config = [
		'template'   => 'layouts/RequisitesMeta/Default.php',
		'listen'     => 'action'
	];

	protected function getLegalForm() {
		$dlLegalForm = new LegalForm();
		return $dlLegalForm->getLegalForm("");
	}

	protected function main() {
		$this->context->css[] = "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css";
		$this->variables->data = [
			'legalForm' => self::getLegalForm()
			];

		$this->variables->legalForm = self::getLegalForm();
	}



}