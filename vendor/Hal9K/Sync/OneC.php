<?php
/**
 * Reregister
 */
namespace Environment\Vendors\Hal9K\Sync;

use Environment\Soap\Types\Requisites\Data\Export\Data as ExportData,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common\Address as ExportCommonAddress,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common\Representative as ExportCommonRepresentative;

use Environment\Soap\Clients as SoapClients;

class OneC extends Trigger {
    const
        ROLES_CHIEF      = 1,
        ROLES_ACCOUNTANT = 2;

    protected function stringifyAddress(ExportCommonAddress $address){
        $result = [];

        $settlement = $address->settlement;

        if($settlement->region){
            $result[] = $settlement->region->name;
        }

        if($settlement->district){
            if($settlement->district->region){
                $result[] = $settlement->district->region->name;
            }

            $result[] = $settlement->district->name;
        }

        $result[] = $settlement->name;

        $result[] = $address->street;
        $result[] = $address->building;

        if($address->apartment){
            $result[] = $address->apartment;
        }

        return implode(', ', $result);
    }

    protected function stringifyRepresenantiveName(ExportCommonRepresentative $rep){
        $result = [];

        $person = $rep->person;

        $result[] = $person->surname;
        $result[] = $person->name;

        if($person->middleName){
            $result[] = $person->middleName;
        }

        return implode(' ', $result);
    }

    protected function stringifyRepresenantivePassport(ExportCommonRepresentative $rep){
        $result = [];

        $passport = $rep->person->passport;

        $result[] = $passport->series . ' ' . $passport->number;
        $result[] = $passport->issuingAuthority;
        $result[] = date('d.m.Y', strtotime($passport->issuingDate));

        return implode(', ', $result);
    }

    public function process(ExportData $requisites){
        $common = $requisites->common;
        $sti    = $requisites->sti;

        $chief      = null;
        $accountant = null;

        foreach($common->representatives as $representative){
            foreach($representative->roles as $role){
                switch($role->id){
                    case self::ROLES_CHIEF:
                        $chief = $representative;
                    break;

                    case self::ROLES_ACCOUNTANT:
                        $accountant = $representative;
                    break;
                }
            }
        }

        $data = [
            'uid'  => $requisites->uid,
            'form' => $common->legalForm->shortName ?: $common->legalForm->name,
            'name' => $common->name,
            'inn'  => $common->inn,
            'gns'  => $sti ? $sti->regionDefault->id : null,
            'okpo' => $common->okpo,

            'urAdres' => $this->stringifyAddress($common->juristicAddress),
            'fAdres'  => $this->stringifyAddress($common->physicalAddress),

            'bank' => $common->bank ? $common->bank->name : null,
            'bik'  => $common->bank ? $common->bank->id : null,
            'rs'   => $common->bankAccount,

            'leader'          => $this->stringifyRepresenantiveName($chief),
            'leaderpasport'   => $this->stringifyRepresenantivePassport($chief),
            'position'        => $chief->position->name,
            'leadertelephone' => $chief->phone,
            'leadermail'      => $common->eMail,
        ];

        if($accountant){
            $data['accountant']        = $this->stringifyRepresenantiveName($accountant);
            $data['accountantpasport'] = $this->stringifyRepresenantivePassport($accountant);
            $data['accountantmail']    = $common->eMail;
            $data['accountantphone']   = $accountant->phone;
        } else {
            $data['accountant']        = null;
            $data['accountantpasport'] = null;
            $data['accountantmail']    = $common->eMail;
            $data['accountantphone']   = null;
        }

        $client = new SoapClients\OneC\Registration();

        $parameters = new \stdClass();

        $parameters->data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $result = $client->registration($parameters);

        if($result->return === 'success'){
            return true;
        } else {
            throw new \Exception($result->return);
        }

        return false;
    }
}
?>
