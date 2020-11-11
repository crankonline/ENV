<?php
namespace Environment\Modules\ArrayExecToExcel;


class AccountInnSystemDealer  extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelDealer
{

    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND "inv"."inn" LIKE :f_inn AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem';
        $this->values = ([
            'f_paymentSystem' => $values['system'],
            'f_account' => '%'.$values['account'].'%',
            'f_inn' => '%'.$values['inn'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }
}
