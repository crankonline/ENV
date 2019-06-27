<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Unikum\Core\DataLayer as Datalayer;

class CountryPassports extends \Environment\Core\Module {
    protected $config = [
        'template'   => 'layouts/CountryPassports/Default.html',
        'listen'     => 'action'
    ];

    protected function getPassports(){
        $params = [ "(document_series = 'AN')" ];
        $values = [];

        if(!empty($_POST['surname'])){
            $params[] = "(person_lastname_kyr LIKE :surname)";

            $values['surname'] = '%' . $_POST['surname'] . '%';
        }

        if(!empty($_POST['name'])){
            $params[] = "(person_firstname_kyr LIKE :name)";

            $values['name'] = '%' . $_POST['name'] . '%';
        }

        if(!empty($_POST['middle-name'])){
            $params[] = "(person_patronymic_kyr LIKE :middleName)";

            $values['middleName'] = '%' . $_POST['middle-name'] . '%';
        }

        if(!$values){
            return null;
        }

        $params[] = '(document_expiredate > GETDATE())';

        $params = 'WHERE ' . implode(' AND ', $params);

        $sql = <<<SQL
SELECT
    person_idnp,
    LOWER(person_firstname_kyr) as person_firstname_kyr,
    LOWER(person_lastname_kyr) as person_lastname_kyr,
    LOWER(person_patronymic_kyr) as person_patronymic_kyr,
    person_birthdate,
    document_series,
    document_number,
    document_issuedate,
    document_expiredate,
    CONVERT(TEXT, document_authority) as document_authority
FROM
    grn
{$params};
SQL;

        set_time_limit(0);

        $stmt = Connections::getConnection('Sarp')->prepare($sql);

        $stmt->execute($values);

        return $stmt;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-country-passports.css';

        $this->variables->errors = [];

        if($_POST){
            try {
                $this->variables->passports = $this->getPassports();
            } catch(\Exception $e) {
	            \Sentry\captureException($e);
                $this->variables->errors[] = $e->getMessage();
            }
        }
    }
}
?>