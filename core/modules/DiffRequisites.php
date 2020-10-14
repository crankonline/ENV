<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;
use Environment\Soap\Clients as SoapClients;
use Environment\Modules\Diff as Diff;

class DiffRequisites extends \Environment\Core\Module{
    const
        ROLES_CHIEF = 1,
        ROLES_CONSULTING = 5,
        ROLES_ROOT = 6;

    protected $config = [
        'template' => 'layouts/DiffRequisites/Default.php',
        'listen'   => 'action'
    ];

    protected function getRequisites( $inn, $upToDateTime = null ) {
        if($upToDateTime) {
            $objDateTime = new \DateTime($upToDateTime);
            $upToDateTime = $objDateTime->format('c');
        }
        $client = new SoapClients\Api\RequisitesData();

        $requisites = $client->getByInn( $client::SUBSCRIBER_TOKEN, $inn, $upToDateTime );

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

    private function prettyPrint( $json ) {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
        }

        return $result;
    }

    protected function main() {
        $this->variables->errors = [];
        $this->context->css[] = 'resources/css/ui-requisites.css';
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-form-colored.css';

        $inn = isset( $_GET['inn'] ) ? $_GET['inn'] : null;
        $date = isset( $_GET['date'] ) ? $_GET['date'] : null;

        if ( ! ( $inn || $date ) ) {
            return;
        }

        if ( isset( $_GET['pretty'] ) ) {
            $requisitesLatest = $this->prettyPrint(json_encode($this->getRequisites($inn), JSON_UNESCAPED_UNICODE));
            $requisitesInReportDate = $this->prettyPrint(json_encode($this->getRequisites($inn, $date), JSON_UNESCAPED_UNICODE));
        } else {
            $requisitesLatest = json_encode($this->getRequisites($inn), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $requisitesInReportDate = json_encode($this->getRequisites($inn, $date), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $diff = Diff\Diff::toTable(Diff\Diff::compare($requisitesLatest,$requisitesInReportDate),'','');

        $this->variables->inn = $inn;
        $this->variables->date = $date;
        $this->variables->diff = $diff;
    }
}
