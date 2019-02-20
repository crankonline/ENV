<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Modules as Modules;

class Services extends \Environment\Core\Module {

    protected $config = [
        'template'   => null,
        'listen'     => 'action'
    ];

	protected function main() {
		exit;
	}

	private function checkIP() {
		$ip = $_SERVER['REMOTE_ADDR'];

		$allowedIPs = [
			'127.0.0.1',
			'172.16.3.5',
			'192.168.1.7'
		];

		return in_array($ip, $allowedIPs);
	}

	private function checkToken() {
		$token = 'ff59d7b81b3c69844de54454e02d6b9b73ae72da30ab7e3b0bcf2353152ae9f5';
		return $token == $_POST['token'];
	}

	public function getBalance() {
		if(!$this->checkIP()) return;
		if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$balance = (new Modules\Sochi())->getBalance($inn);
		echo $balance;
	}

	public function getAccruals() {
		if(!$this->checkIP()) return;
		if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$data = (new Modules\Sochi())->getAccruals($inn);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	public function getBills() {
		if(!$this->checkIP()) return;
		if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$data = (new Modules\Sochi())->getBills($inn);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	public function getUser(){
		if(!$this->checkIP()) return;
		if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$data = (new Modules\Sochi())->getUser($inn, null);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getSfReports() {
    	if(!$this->checkIP()) return;
    	if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$user = (new Modules\Sochi())->getUser($inn, null);
		$data = (new Modules\Sochi())->getSfReports($user['inn'], $user['uid']);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getStiReports() {
    	if(!$this->checkIP()) return;
    	if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$user = (new Modules\Sochi())->getUser($inn, null);
		$data = (new Modules\Sochi())->getStiReports($user['inn'], $user['uid']);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getNscReports() {
    	if(!$this->checkIP()) return;
    	if(!$this->checkToken()) return;
		$inn = $_POST['inn'];
		$user = (new Modules\Sochi())->getUser($inn, null);
		$data = (new Modules\Sochi())->getNscReports($user['uid']);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

}
