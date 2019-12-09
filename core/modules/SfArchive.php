<?php


namespace Environment\Modules;


use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Soap\Clients as SoapClients;

class SfArchive extends \Environment\Core\Module
{
    const
        ROLES_CHIEF = 1,
        ROLES_CONSULTING = 5,
        ROLES_ROOT = 6;

    protected $config = [
        'template' => 'layouts/SfArchive/Default.php',
        'listen' => 'action'
    ];

    protected function getRequisites( $inn, $uid ) {
        $client = new SoapClients\Api\RequisitesData();

        $requisites = $uid
            ? $client->getByUid( $client::SUBSCRIBER_TOKEN, $uid, null )
            : $client->getByInn( $client::SUBSCRIBER_TOKEN, $inn, null );

        if ( ! ( $requisites && $requisites->common ) ) {
            throw new \Exception( 'Клиент не найден' );
        }

        $consulting = null;

        foreach ( $requisites->common->representatives as $rep ) {
            foreach ( $rep->roles as $role ) {
                switch ( $role->id ) {
                    case self::ROLES_CONSULTING:
                        $consulting = $rep->person->passport;
                        break 2;

                    case self::ROLES_ROOT:
                        $consulting = $rep->person->passport;
                        break 2;
                }
            }
        }

        $bindings = $consulting
            ? $client->getConsultingBindingsByPassport(
                $client::SUBSCRIBER_TOKEN,
                $consulting->series,
                $consulting->number
            )
            : null;

        return [ $requisites, $bindings ];
    }

    protected function getSfArchive($inn) {
        $sql = <<<SQL
SELECT * FROM sf_reports.pass_reports
WHERE inn = :inn
ORDER BY input_date
SQL;

        $stmt = Connections::getConnection( 'SfArchive' )->prepare( $sql );

        $stmt->execute( [
            'inn' => $inn
        ] );

        return $stmt->fetchAll();
    }

    protected function getSfArchiveReport($uin) {
        $sql = <<<SQL
SELECT * FROM sf_reports.reports_data
WHERE uin = :uin
SQL;

        $stmt = Connections::getConnection( 'SfArchive' )->prepare( $sql );

        $stmt->execute( [
            'uin' => $uin
        ] );

        return $stmt->fetch();
    }

    protected function main()
    {

        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-form-colored.css';
        $this->context->css[] = 'resources/css/ui-requisites.css';

        $this->variables->errors = [];

        $inn = isset( $_GET['inn'] ) ? $_GET['inn'] : null;
        $uid = isset( $_GET['uid'] ) ? $_GET['uid'] : null;

        if ( ! ( $inn || $uid ) ) {
            return;
        }

        if ( $inn && ! preg_match( '/^(\d{10,10})|(\d{14,14})$/', $inn ) ) {
            $this->variables->errors[] = 'ИНН должен состоять из 10 или 14 цифр';

            return;
        }

        if ( $uid && ! preg_match( '/^\d{23,23}$/', $uid ) ) {
            $this->variables->errors[] = 'UID должен состоять из 23 цифр';

            return;
        }

        try {
            $requisitesAll = $this->getRequisites( $inn, $uid );
            list( $requisites, $bindings ) = $requisitesAll;
        } catch ( \SoapFault $e ) {
//			\Sentry\captureException( $e );
            $this->variables->errors[] = $e->faultstring;

            return;
        } catch ( \Exception $e ) {
//			\Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();

            return;
        }

        $this->variables->requisites = $requisites;
        $this->variables->bindings   = $bindings;

        if ( isset( $_POST['uinSfArchDownload'] ) ) {
            $uinSfArchReportUin = isset( $_POST['uinSfArch'] ) ? $_POST['uinSfArch'] : null;
            try {
                if ($uinSfArchReportUin != null) {
                    $rep = $this->getSfArchiveReport($uinSfArchReportUin);
                    $this->variables->xml = $rep['xml_data'];

                }
            } catch ( \Exception $e ) {
                \Sentry\captureException( $e );
                $this->variables->errors[] = $e->getMessage();
            }
        }
        try {
            $sfArchive = $this->getSfArchive($inn);
            $this->variables->sfArchive = $sfArchive;
//            echo "<pre>";
//            print_r($requisites);
//            print_r($bindings);
//            print_r($sfArch);
//            echo "</pre>";
//            die();
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $this->variables->errors[] = $e->getMessage();
        }
    }
}